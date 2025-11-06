<?php

namespace App\Providers;

use Spatie\Permission\PermissionRegistrar;
use App\Models\Branch;
use Illuminate\Support\ServiceProvider;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TomatoPHP\FilamentInvoices\Facades\FilamentInvoices;
use TomatoPHP\FilamentInvoices\Services\Contracts\InvoiceFor;
use TomatoPHP\FilamentInvoices\Services\Contracts\InvoiceFrom;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        TextColumn::configureUsing(function (TextColumn $column) {
            $column->formatStateUsing(fn($state) => is_numeric($state) ? number_format((float) $state) : $state);
        });

        // ترتيب افتراضي لكل الجداول في Filament
        Table::configureUsing(function (Table $table): void {
            $table->defaultSort('created_at', 'desc');
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->visible(outsidePanels: true)
                ->locales(['ar', 'en']); // also accepts a closure
        });

        app(PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        /* FilamentInvoices::registerFor([
            InvoiceFor::make(User::class)
                ->label('Account')
        ]);
        FilamentInvoices::registerFrom([
            InvoiceFrom::make(Branch::class)
                ->label('Company')
        ]); */

        setlocale(LC_NUMERIC, 'en_US.UTF-8');

        Resource::scopeToTenant(false);

        /*DB::listen(function ($query) {
            Log::info(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });*/
    }
}
