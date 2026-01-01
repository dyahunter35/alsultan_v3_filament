<?php

namespace App\Filament\Resources\Trucks\Widgets;

use App\Models\Truck;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TruckFinanceOverview extends StatsOverviewWidget
{
    public ?Truck $record = null; // inject record automatically

    protected function getStats(): array
    {
        $expenses = $this->record?->expenses() ?? collect();

        $totalExpenses = $expenses->sum('total_amount');
        $paid = $expenses->where('is_paid', true)->sum('total_amount');
        $remaining = $totalExpenses - $paid;

        return [
            Stat::make('Total Expenses', number_format($totalExpenses, 2))
                ->color('primary')
                ->description('Total for this truck'),

            Stat::make('Paid', number_format($paid, 2))
                ->color('success')
                ->description('Paid expenses'),

            Stat::make('Unpaid', number_format($remaining, 2))
                ->color('danger')
                ->description('Remaining unpaid'),
        ];
    }
}
