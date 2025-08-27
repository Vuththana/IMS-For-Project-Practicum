<?php

namespace App\Observers\Partner;

use App\Models\Partner\Supplier;
use Illuminate\Support\Facades\Auth;

class SupplierCompanyCreate
{
    public function creating(Supplier $supplier): void
    {
        if (Auth::check() && empty($supplier->company_id)) {
            $supplier->company_id = Auth::user()->current_company_id;
        }
    }
}
