<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyProfile extends Model
{
    protected $table = 'company_profiles';
    
    protected $fillable = [        
    'logo',
    'name',
    'email',
    'phone_number',
    'street_address',
    'city',
    'state',
    'postal_code',
    'country',
    'company_id',
    'created_by',
    'updated_by',
];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}