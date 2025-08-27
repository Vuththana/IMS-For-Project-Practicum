<?php

namespace App\Observers\Sale;

use App\Models\Sale\Sale;
use Illuminate\Support\Facades\Auth;

class SaleCompanyCreate
{
    public function creating(Sale $sale): void
    {
        if (Auth::check() && empty($sale->company_id)) {
            $sale->company_id = Auth::user()->current_company_id;
        }
    }

}
