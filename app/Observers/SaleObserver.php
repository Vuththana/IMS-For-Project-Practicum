<?php

namespace App\Observers;

use App\Models\Sale\Sale;

class SaleObserver
{
    public function created(Sale $sale)
    {
        foreach ($sale->saleItems as $item) {
            $product = $item->product;
            if ($product) {
                $product->stock -= $item->quantity;
                $product->save();
            }
        }
    }
}
