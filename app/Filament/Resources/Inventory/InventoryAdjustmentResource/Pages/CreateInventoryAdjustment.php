<?php

namespace App\Filament\Resources\Inventory\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\Inventory\InventoryAdjustmentResource;
use App\Models\Inventory\InventoryAdjustment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryAdjustment extends CreateRecord
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var InventoryAdjustment $adjustment */
        $adjustment = $this->record;

        // Ensure relationships are loaded
        $adjustment->load('batch', 'product');

        // Finalize the adjustment — update batch quantity and create StockMovement logs
        $adjustment->finalizeAdjustment();
    }
}
