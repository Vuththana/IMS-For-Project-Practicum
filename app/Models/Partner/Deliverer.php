<?php

namespace App\Models\Partner;

use App\Models\Company;
use App\Models\Sale\Sale;
use App\Models\Scopes\CompanyScope;
use App\Models\User;
use App\Observers\Partner\DelivererCompanyCreate;
use App\Observers\Partner\DeliveryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ObservedBy(DelivererCompanyCreate::class)]
class Deliverer extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'phone_number',
        'email',
        'address',
        'created_by',
        'updated_by',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function users() {
        return $this->hasMany(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sales(): HasMany {
        return $this->hasMany(Sale::class);
    }

}
