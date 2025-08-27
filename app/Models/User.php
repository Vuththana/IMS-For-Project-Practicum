<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;
    
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'avatar',
    ];

 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->assignRole('Admin');
        });
    }
    
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }
    public function canAccessPanel(Panel $panel): bool {
        return $this->hasRole(['Admin']);
    }
    
}
