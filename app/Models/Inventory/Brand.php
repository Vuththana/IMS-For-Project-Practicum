<?php

namespace App\Models\Inventory;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\BrandCompanyCreate;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ObservedBy([BrandCompanyCreate::class])]
class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name'];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
