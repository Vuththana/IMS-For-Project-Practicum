<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Brand;
use Illuminate\Support\Facades\Auth;

class BrandCompanyCreate
{
    public function creating(Brand $category): void
    {
        if (Auth::check() && empty($category->company_id)) {
            $category->company_id = Auth::user()->current_company_id;
        }
    }
}
