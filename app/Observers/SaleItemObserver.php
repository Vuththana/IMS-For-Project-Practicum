<?php

namespace App\Observers;

use App\Models\Sale\Sale;
use App\Models\Sale\SaleItem;

class SaleItemObserver
{
    public function creating(SaleItem $saleItem): void
    {
        // Assign sale_id if not already set
        if (empty($saleItem->sale_id)) {
            $sale = Sale::latest()->first();
            $saleItem->sale_id = $sale->id;
        }

        // Get product's current unit_cost
        $unitCost = $saleItem->product->unit_cost ?? 0;

        // Calculate COGS for this line
        $saleItem->cogs = round($unitCost * $saleItem->quantity, 2);
    }
}
