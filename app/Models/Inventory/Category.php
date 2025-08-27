<?php

namespace App\Models\Inventory;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\CategoryCompanyCreate;
use App\Observers\Inventory\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ObservedBy(CategoryCompanyCreate::class)]
class Category extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

}
