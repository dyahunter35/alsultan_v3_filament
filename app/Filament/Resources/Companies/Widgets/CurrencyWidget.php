<?php

namespace App\Filament\Resources\Companies\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrencyWidget extends StatsOverviewWidget
{
    public ?Company $record = null;


    protected function getStats(): array
    {

        $currencies = \App\Models\Currency::pluck('name', 'id');
        foreach ($currencies as $id => $name) {
            $total = $this->record->currencyTransactions()->where('currency_id', $id)->sum('amount');

            $stats[] = Stat::make($name, number_format($total, 2))
                ->descriptionIcon('heroicon-o-currency-dollar');
        }
        return $stats;
    }
}
