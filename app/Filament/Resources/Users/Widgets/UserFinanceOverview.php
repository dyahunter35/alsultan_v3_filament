<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserFinanceOverview extends StatsOverviewWidget
{
    public ?User $record = null;

    protected function getStats(): array
    {
        $user = $this->record;

        /* echo $user->total_paid;        // بدل totalPaid()
        echo $user->total_received;
        echo $user->total_supplyings;
        echo $user->total_orders;
        echo $user->net_balance; */
        return [
            Stat::make('Total Paid', number_format($user->total_paid, 2))
                ->color('primary')
                ->description('المدفوعات'),

            Stat::make('Total Received', number_format($user->total_received, 2))
                ->color('primary')
                ->description('الاستلامات'),

            Stat::make('Total Supplyings', number_format($user->total_supplyings, 2))
                ->color('primary')
                ->description('التوريدات'),

            Stat::make('Sales', number_format($user->total_orders, 2))
                ->color('success')
                ->description('المبيعات'),

            Stat::make('Net Balance', number_format($user->net_balance, 2))
                ->color(fn () => ($user->net_balance > 0) ? 'danger' : 'success')
                ->description('الصافي النهائي'),
        ];
    }
}
