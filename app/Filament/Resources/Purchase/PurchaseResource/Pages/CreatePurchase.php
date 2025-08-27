<?php

namespace App\Filament\Resources\Purchase\PurchaseResource\Pages;

use App\Filament\Resources\Purchase\PurchaseResource;
use App\Models\Purchase\Purchase;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    // protected function afterCreate(): void --> Might be useful in future updates
    // {
    //     /** @var Purchase $purchase */
    //     $purchase = $this->record;

    //     // Ensure relationships are loaded
    //     $purchase->load('purchaseItems.product');

    //     // Finalize the purchase — update stock and create StockMovement logs
    //     $purchase->finalizePurchase('purchase');
    // }
}
