<?php

namespace App\Filament\Resources\Inventory\StockMovementResource\Pages;

use App\Filament\Resources\Inventory\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
        ];
    }
}
