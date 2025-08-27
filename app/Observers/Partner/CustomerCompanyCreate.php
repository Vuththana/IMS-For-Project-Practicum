<?php

namespace App\Observers\Partner;

use App\Models\Partner\Customer;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Support\Facades\Auth;

class CustomerCompanyCreate
{
    public function creating(Customer $customer): void
    {
        if (Auth::check() && empty($customer->company_id)) {
            $customer->company_id = Auth::user()->current_company_id;
        }
    }
}
