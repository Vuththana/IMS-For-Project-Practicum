<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Product;
use Illuminate\Support\Facades\Auth;

class ProductCompanyCreate
{
    public function creating(Product $product): void
    {
        if (Auth::check() && empty($product->company_id)) {
            $product->company_id = Auth::user()->current_company_id;
        }
    }
}
