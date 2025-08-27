<?php

namespace App\Filament\Resources\Sale\SalesResource\Pages;

use App\Filament\Resources\Sale\SalesResource;
use App\Models\Sale\Sale;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSales extends EditRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        /** @var Sale $sale */
        
        $sale = $this->record;
        
        $sale->load('saleItems.product');
        // Revert previous stock movements
        $sale->revertStock();
    
        // Apply updated stock + log update
        $sale->finalizeSale('sale_update');
    }
}
