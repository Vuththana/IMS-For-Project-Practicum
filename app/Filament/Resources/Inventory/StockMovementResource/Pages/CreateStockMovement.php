<?php

namespace App\Filament\Resources\Inventory\StockMovementResource\Pages;

use App\Filament\Resources\Inventory\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
}
