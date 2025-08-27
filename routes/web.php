<?php

use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Partner\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Partner\DelivererController;
use App\Http\Controllers\Sale\CashRegisterController;
use App\Http\Controllers\Sale\InvoiceController;
use App\Http\Middleware\CheckCurrentCompany;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleItem;
use App\Observers\SaleItemObserver;
use Filament\Facades\Filament;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Laravel Socialite
Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubController::class, 'callback']);


Route::middleware(['auth'])->group(function () {
    Route::get('/company/create', [CompanyController::class, 'create'])
        ->name('company.create')
        ->middleware(CheckCurrentCompany::class);
    
    Route::post('/company/store', [CompanyController::class, 'store'])
        ->name('company.store');

            // 3. This route handles the creation of a new Customer
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        
            // 4. This route handles the creation of a new Deliverer
    Route::post('/deliverers', [DelivererController::class, 'store'])->name('deliverers.store');

    Route::post('/sales', [CashRegisterController::class, 'store'])->name('sales.store');

    Route::get('/admin/cash-register', [CashRegisterController::class, 'index'])->name('admin.cash_register');
});


// Logout Route making sure there's no bug
Route::post('/logout', function () {
    // Log out the user
    auth()->logout();

    // Clear the session to prevent any session related issues
    session()->invalidate();
    session()->regenerateToken();

    // Get the Filament login URL
    $loginUrl = Filament::getLoginUrl();

    // Use Inertia to perform a client-side redirect to the login page
    return Inertia::location($loginUrl);
})->name('logout');

Route::get('/login', function () {

    $loginUrl = Filament::getLoginUrl();
    return Inertia::location($loginUrl);
})->name('login');

Route::get('api/states/{country_id}', [CompanyController::class, 'getStates']);
Route::get('api/cities/{country}/{state}', [CompanyController::class, 'getCities']);