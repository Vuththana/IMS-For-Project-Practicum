<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Category;
use Illuminate\Support\Facades\Auth;

class CategoryCompanyCreate
{
    public function creating(Category $category): void
    {
        if (Auth::check() && empty($category->company_id)) {
            $category->company_id = Auth::user()->current_company_id;
        }
    }
}
