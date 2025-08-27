<?php

namespace App\Observers\Partner;

use App\Models\Partner\Deliverer;
use Illuminate\Support\Facades\Auth;

class DelivererCompanyCreate
{
    public function creating(Deliverer $deliverer): void
    {
        if (Auth::check() && empty($deliverer->company_id)) {
            $deliverer->company_id = Auth::user()->current_company_id;
        }
    }
}
