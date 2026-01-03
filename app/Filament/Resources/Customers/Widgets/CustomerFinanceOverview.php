<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerFinanceOverview extends StatsOverviewWidget
{
    public ?Customer $record = null;

    protected int|array|null $columns = [
        'md' => 3,
        'xl' => 5,
    ];

    protected function getHeading(): ?string
    {
        return __('customer.widgets.stats.label');
    }

    protected function getStats(): array
    {
        $c = $this->record;

        if (! $c) {
            return [];
        }

        return [
            Stat::make('Total Paid', number_format($c->total_paid ?? 0, 2))
                ->color('primary')
                ->description('المدفوعات'),

            Stat::make('Total Received', number_format($c->total_received ?? 0, 2))
                ->color('primary')
                ->description('الاستلامات'),

            Stat::make('Total Supplyings', number_format($c->total_supplyings ?? 0, 2))
                ->color('primary')
                ->description('التوريدات'),

            Stat::make('Sales', number_format($c->total_sales ?? 0, 2))
                ->color('success')
                ->description('المبيعات'),

            Stat::make('Net Balance', number_format($c->net_balance ?? 0, 2))
                ->color(fn () => ($c->net_balance > 0) ? 'danger' : 'success')
                ->description('الصافي النهائي'),
        ];
    }
}
