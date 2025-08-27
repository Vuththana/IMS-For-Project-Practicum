<?php

namespace App\Filament\Resources\Inventory\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\Inventory\InventoryAdjustmentResource;
use App\Models\Inventory\InventoryAdjustment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryAdjustment extends EditRecord
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        // Load necessary relationships before update
        $this->record->load('product');
    }

    protected function afterSave(): void
    {
        /** @var InventoryAdjustment $adjustment */
        
        $adjustment = $this->record;
        
        $adjustment->load('batch', 'product');
        // Revert previous stock movements
        $adjustment->revertStock();
    }
}
