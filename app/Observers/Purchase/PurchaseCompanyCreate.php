<?php

namespace App\Observers\Purchase;

use App\Models\Purchase\Purchase;
use Illuminate\Support\Facades\Auth;

class PurchaseCompanyCreate
{
    public function creating(Purchase $purchase): void
    {
        if (Auth::check() && empty($sale->company_id)) {
            $purchase->company_id = Auth::user()->current_company_id;
        }
    }
}