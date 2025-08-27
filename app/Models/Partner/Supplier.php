<?php

namespace App\Models\Partner;

use App\Models\Company;
use App\Models\Purchase\Purchase;
use App\Models\Scopes\CompanyScope;
use App\Observers\Partner\SupplierCompanyCreate;
use App\Observers\Partner\SupplierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ObservedBy(SupplierCompanyCreate::class)]
class Supplier extends Model
{

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'tax_number',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
 

}
