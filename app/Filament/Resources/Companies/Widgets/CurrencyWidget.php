<?php

namespace App\Filament\Resources\Companies\Widgets;

use App\Enums\ExpenseGroup;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class CurrencyWidget extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected int|array|null $columns = [
        'md' => 3,
        'xl' => 5,
    ];

    protected function getHeading(): ?string
    {
        return __('currency.widgets.state.label');
    }

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        if ($this->record instanceof Customer && $this->record?->permanent != ExpenseGroup::CURRENCY) {
            return [];
        }

        // 🟢 تحميل العملات + منع N+1
        $balances = $this->record->currencyBalance()->with('currency')->get();

        if ($balances->isEmpty()) {
            return [
                Stat::make('No Currencies', '0.00')
                    ->description('لا توجد أرصدة عملات')
                    ->color('gray'),
            ];
        }

        $stats = [];

        foreach ($balances as $balance) {
            $name = $balance->currency?->name ?? 'Unknown';
            $amount = $balance->amount ?? 0;

            $stats[] = Stat::make($name, number_format($amount, 2))
                ->color($amount < 0 ? 'danger' : 'success')
                ->description($amount < 0 ? 'رصيد مدين' : 'رصيد دائن')
                ->descriptionIcon('heroicon-o-currency-dollar');
        }

        return $stats;
    }
}
