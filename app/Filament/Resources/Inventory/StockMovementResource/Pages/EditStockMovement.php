<?php

namespace App\Filament\Resources\Inventory\StockMovementResource\Pages;

use App\Filament\Resources\Inventory\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
