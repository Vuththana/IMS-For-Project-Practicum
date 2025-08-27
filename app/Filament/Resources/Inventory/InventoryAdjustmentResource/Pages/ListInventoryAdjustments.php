<?php

namespace App\Filament\Resources\Inventory\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\Inventory\InventoryAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryAdjustments extends ListRecords
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
