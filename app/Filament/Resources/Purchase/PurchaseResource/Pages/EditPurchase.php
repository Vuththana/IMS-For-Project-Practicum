<?php

namespace App\Filament\Resources\Purchase\PurchaseResource\Pages;

use App\Filament\Resources\Purchase\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\Purchase\Purchase $purchase */
        $purchase = $this->record;

        // Ensure relationships are loaded
        $purchase->load('purchaseItems.product');

        $purchase->revertStock();

        // Finalize the sale — update stock and create StockMovement logs
        $purchase->finalizePurchase('purchase_update');
    }
}
