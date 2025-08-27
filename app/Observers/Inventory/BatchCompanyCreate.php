<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Batch;
use Illuminate\Support\Facades\Auth;

class BatchCompanyCreate
{
    public function creating(Batch $batch): void
    {
        if (Auth::check() && empty($batch->company_id)) {
            $batch->company_id = Auth::user()->current_company_id;
        }
    }
}
