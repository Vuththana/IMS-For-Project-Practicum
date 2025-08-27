<?php

namespace App\Filament\Pages\Register;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Redirect; // Ensure Redirect facade is imported
use Illuminate\Contracts\Support\Htmlable; // For getTitle() type hint

class CashRegister extends Page
{
    // Remove or comment out this line:
    // protected static string $view = 'filament.pages.register.cash-register';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    // Add navigation properties so it appears in the Filament sidebar
    protected static ?string $navigationLabel = 'Cash Register Mode';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'cash-register-link';

    public function mount(): void
    {
        redirect('/admin/cash-register');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Redirecting to Cash Register...';
    }
}