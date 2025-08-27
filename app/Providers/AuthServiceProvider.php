<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Company;
use App\Models\User;
use App\Policies\CompanyPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Company::class => CompanyPolicy::class,
    ];
    /**
     * Register any authentication / authorization services.
     */
    public function register(): void
    {
        // You can bind interfaces to implementations here if needed
    }

    /**
     * Bootstrap any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define custom gates for access control
        Gate::define('edit-company', function (User $user, Company $company) {
            return $user->company_id === $company->id;
        });

        // Example for creating a custom gate to manage company editing
        Gate::define('create-company', function (User $user) {
            return is_null($user->company_id);
        });
    }
}
