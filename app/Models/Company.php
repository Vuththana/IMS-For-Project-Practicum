<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $casts = [
        'personal_company' => 'boolean',
    ];
    
    protected $fillable = ['user_id', 'name', 'personal_company'];
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function profile()
    {
        return $this->hasOne(CompanyProfile::class, 'company_id');
    }

    public function setting()
{
    return $this->hasOne(Setting::class);
}
}
