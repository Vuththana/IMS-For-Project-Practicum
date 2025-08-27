<?php

namespace App\Providers\Filament;

use App\Filament\Clusters\Settings;
use App\Filament\Pages\Register\CashRegister;
use App\Filament\Resources\Inventory\BatchesResource;
use App\Filament\Resources\Inventory\BrandResource;
use App\Filament\Resources\Partner\DelivererResource;
use App\Filament\Resources\Inventory\CategoryResource;
use App\Filament\Resources\Inventory\ProductResource;
use App\Filament\Resources\Inventory\StockMovementResource;
use App\Filament\Resources\Partner\SupplierResource;
use App\Filament\Resources\Purchase\PurchaseResource;
use App\Filament\Resources\Sale\SalesResource;
use App\Http\Middleware\CheckCurrentCompany;
use ChrisReedIO\Socialment\SocialmentPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Pages\Auth\Login;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Resources\Inventory\InventoryAdjustmentResource;
use App\Filament\Resources\Inventory\SubCategoryResource;
use App\Filament\Resources\Partner\CustomerResource;
use Rupadana\ApiService\ApiServicePlugin;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->profile()
            ->databaseNotifications()
            ->passwordReset(RequestPasswordReset::class) 
            ->plugins([
                \Hasnayeen\Themes\ThemesPlugin::make(), 
                TwoFactorAuthenticationPlugin::make()
                    ->addTwoFactorMenuItem(),
                ApiServicePlugin::make()
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigation(function(NavigationBuilder $builder) {
                return $builder
                    ->items([
                    ...Dashboard::getNavigationItems(),
                    ...Settings::getNavigationItems(),
                    ])
                    ->groups([

                        NavigationGroup::make('Inventory')
                            ->label('Inventory')
                            ->icon('heroicon-o-currency-dollar')
                            ->items([
                                ...CategoryResource::getNavigationItems(),
                                ...SubCategoryResource::getNavigationItems(),
                                ...BrandResource::getNavigationItems(),
                                ...ProductResource::getNavigationItems(),
                                ...BatchesResource::getNavigationItems(),
                                ...InventoryAdjustmentResource::getNavigationItems(),
                                ...StockMovementResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Partner')
                            ->label('Partner')
                            ->icon('heroicon-c-user-group')
                            ->items([
                                ...CustomerResource::getNavigationItems(),
                                ...DelivererResource::getNavigationItems(),
                                ...SupplierResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Sales')
                            ->label('Sales')
                            ->icon('heroicon-o-shopping-cart')
                            ->items([
                                ...SalesResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Expense')
                            ->label('Expense')
                            ->icon('heroicon-o-currency-dollar')
                            ->items([
                                ...PurchaseResource::getNavigationItems(),
                        ]),
                        NavigationGroup::make('Register')
                            ->label('Register')
                            ->icon('heroicon-o-shopping-cart')
                            ->items([
                                ...CashRegister::getNavigationItems(),
                        ]),
                    ]);
            })
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->brandName('FlowPOS')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckCurrentCompany::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
