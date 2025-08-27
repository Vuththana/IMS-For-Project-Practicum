<?php

namespace App\Models\Inventory;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\SubCategoryCompanyCreate;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[ObservedBy([SubCategoryCompanyCreate::class])]
class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'category_id', 'name'];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
