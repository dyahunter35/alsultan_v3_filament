<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerFinanceOverview extends StatsOverviewWidget
{
    public ?Customer $record = null;

    protected function getStats(): array
    {
        $customer = $this->record;

        return [
            Stat::make('Total Paid', number_format($customer->total_paid, 2))
                ->color('primary')
                ->description('المدفوعات'),

            Stat::make('Total Received', number_format($customer->total_received, 2))
                ->color('primary')
                ->description('الاستلامات'),

            Stat::make('Total Supplyings', number_format($customer->total_supplyings, 2))
                ->color('primary')
                ->description('التوريدات'),

            Stat::make('Sales', number_format($customer->total_sales, 2))
                ->color('success')
                ->description('المبيعات'),

            Stat::make('Net Balance', number_format($customer->net_balance, 2))
                ->color(fn() => ($customer->net_balance > 0) ? 'danger' : 'success')
                ->description('الصافي النهائي'),
        ];
    }
}
