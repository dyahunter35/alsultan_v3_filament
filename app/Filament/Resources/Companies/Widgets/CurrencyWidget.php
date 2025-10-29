<?php

namespace App\Filament\Resources\Companies\Widgets;

use App\Models\Company;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class CurrencyWidget extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {

        $currencies = $this->record?->currencyBalance;
        $stats = [];
        foreach ($currencies as $currency) {
            $total = $currency->amount;
            $stats[] = Stat::make($currency->currency->name, number_format($total, 2))
                ->descriptionIcon('heroicon-o-currency-dollar');
        }
        return $stats;
    }
}
