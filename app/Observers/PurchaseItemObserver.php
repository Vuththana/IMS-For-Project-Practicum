<?php

// app/Observers/PurchaseItemObserver.php
namespace App\Observers;

use App\Models\Purchase\PurchaseItem;

class PurchaseItemObserver
{

    public function created(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;
    
        $purchases = PurchaseItem::where('product_id', $product->id)->get();
    
        $totalQty  = $purchases->sum('quantity');
        $totalCost = $purchases->sum(function ($item) {
            return $item->quantity * $item->unit_cost;
        });
    
        $avgCost = $totalQty > 0 ? $totalCost / $totalQty : 0;
    
        $product->update([
            'unit_cost' => round($avgCost, 2),
            'quantity'  => $totalQty,
        ]);
    }
    
}
