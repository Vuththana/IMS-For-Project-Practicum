<?php

namespace App\Filament\Resources\Sale\SalesResource\Pages;

use App\Filament\Resources\Sale\SalesResource;
use App\Models\Sale\Sale;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function afterCreate(): void
    {
        /** @var Sale $sale */
        $sale = $this->record;

        // Ensure relationships are loaded
        $sale->load('saleItems.product');

        // Finalize the sale — update stock and create StockMovement logs
        $sale->finalizeSale('sale');
    }
}
