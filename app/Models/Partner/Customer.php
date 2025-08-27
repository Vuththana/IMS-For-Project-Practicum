<?php

namespace App\Models\Partner;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Observers\Partner\CustomerCompanyCreate;
use App\Observers\Partner\CustomerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[ObservedBy(CustomerCompanyCreate::class)]
class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'address',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
