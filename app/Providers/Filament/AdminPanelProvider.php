<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Profile;
use App\Filament\Pages\Dashboard\MainDashboard;
use App\Filament\Pages\Tenancy\EditBranch;
use App\Filament\Pages\Tenancy\RegisterBranch;
use App\Filament\Resources\Products\Pages\BranchReport;
use App\Filament\Resources\Products\Pages\ProductStockReport;
use App\Models\Branch;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->spa()
            ->profile(Profile::class)
            ->font('Poppins')
            ->databaseTransactions()
            // ->databaseNotifications()
            ->brandName(fn () => __('app.name'))
            // ->brandLogo(fn  ()=>asset('asset/images/logo/gas 200.png'))
            ->favicon(asset('asset/logo.png'))
            // ->brandLogo(asset('asset/logo.png'))
            ->tenant(Branch::class, slugAttribute: 'slug')
            ->tenantRegistration(RegisterBranch::class)
            ->tenantProfile(EditBranch::class)
            ->collapsibleNavigationGroups(true)
            ->defaultThemeMode(ThemeMode::Light)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                'panels::head.end', // ⬅ يضيف قبل </head>
                fn (): string => <<<'HTML'
                        <link rel="manifest" href="/manifest.json">
                        <meta name="theme-color" content="#0f172a">
                        <script>
                            if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.register('/service-worker.js')
                                    .then(() => console.log("Service Worker Registered"));
                            }
                        </script>
                    HTML
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                MainDashboard::class,
                // BranchReport::class,
                // ProductStockReport::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
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
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup(fn () => __('user.navigation.group', [], app()->getLocale()))                  // string|Closure|null
                    ->navigationSort(10)
                    ->scopeToTenant(false)                       // bool|Closure
                    ->tenantRelationshipName(null)    // string|Closure|null
                    ->tenantOwnershipRelationshipName(null) // string|Closure|null
                    // int|Closure|null
                    // ->navigationBadge('5')                      // string|Closure|null
                    // ->navigationBadgeColor('success')           // string|array|Closure|null
                    // ->navigationParentItem('parent.item')       // string|Closure|null
                    ->registerNavigation(true)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),

                // \TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin::make()
            ])
            ->tenantMiddleware([
                // SyncShieldTenant::class,
            ], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
