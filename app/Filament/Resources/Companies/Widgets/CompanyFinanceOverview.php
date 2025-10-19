<?php

namespace App\Filament\Resources\Companies\Widgets;

use App\Enums\CompanyType;
use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyFinanceOverview extends StatsOverviewWidget
{
    public ?Company $record = null;

    protected function getStats(): array
    {
        $expenses = $this->record?->expenses() ?? collect();

        $total = $expenses->sum('expenses.total_amount');
        $paid = $expenses->where('is_paid', true)->sum('expenses.total_amount');
        $remaining = $total - $paid;

        $truckCount = ($this->record->type == CompanyType::Company) ?
            $this->record?->trucksAsCompany()->count() ?? 0 :
            $this->record?->trucksAsContractor()->count() ?? 0;

        return [
            Stat::make('Total Expenses', number_format($total, 2))
                ->color('primary')
                ->description('All trucks total'),

            Stat::make('Paid', number_format($paid, 2))
                ->color('success')
                ->description('Paid expenses'),

            Stat::make('Unpaid', number_format($remaining, 2))
                ->color('danger')
                ->description('Remaining unpaid'),

            Stat::make('Trucks', $truckCount)
                ->color('info')
                ->description('Total trucks for this company'),
        ];
    }
}
