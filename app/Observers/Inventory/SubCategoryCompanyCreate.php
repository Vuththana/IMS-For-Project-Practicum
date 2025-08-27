<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\SubCategory;
use Illuminate\Support\Facades\Auth;

class SubCategoryCompanyCreate
{
    public function creating(SubCategory $category): void
    {
        if (Auth::check() && empty($category->company_id)) {
            $category->company_id = Auth::user()->current_company_id;
        }
    }
}
