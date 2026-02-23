<?php

namespace App\Filament\Widgets;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\CurrencyTransaction;
use App\Models\Truck;
use App\Models\TruckCargo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CompanyFinancialStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [];
        // 1. Factories Financials (Debtors)
        /* $factoryIds = Company::company()->pluck('id');

        $factoriesDebt = TruckCargo::whereHas('truck', function ($query) use ($factoryIds) {
            $query->whereIn('company_id', $factoryIds);
        })->sum(DB::raw('ton_price * ton_weight'));

        $factoriesPaid = CurrencyTransaction::whereIn('party_id', $factoryIds)
            ->where('party_type', Company::class)
            ->sum('total');

        $factoriesNet = $factoriesDebt - $factoriesPaid;

        // 2. Contractors Financials (Creditors from Sultan's perspective)
        $contractorIds = Company::contractor()->pluck('id');

        $contractorsClaims = Truck::whereIn('contractor_id', $contractorIds)
            ->sum('total_amount');

        $contractorsPaid = CurrencyTransaction::whereIn('party_id', $contractorIds)
            ->where('party_type', Company::class)
            ->sum('total');

        $contractorsNet = $contractorsClaims - $contractorsPaid;

        return [
            Stat::make('مديونية المصانع', number_format($factoriesNet, 2) . ' EGY')
                ->description('المبلغ المتبقي لدى شركات الشحن (المصانع)')
                ->descriptionIcon($factoriesNet > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($factoriesNet > 0 ? 'warning' : 'success'),

            Stat::make('حقوق المقاولين', number_format($contractorsNet, 2) . ' EGY')
                ->description('المبلغ المتبقي لمقاولي الشحن (الديون الخارجية)')
                ->descriptionIcon($contractorsNet > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($contractorsNet > 0 ? 'danger' : 'success'),

            Stat::make('صافي الرصيد المالي للشحن', number_format($factoriesNet - $contractorsNet, 2) . ' EGY')
                ->description('الفرق بين مديونية المصانع وحقوق المقاولين')
                ->color(($factoriesNet - $contractorsNet) >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-scale'),
        ]; */
    }
}
