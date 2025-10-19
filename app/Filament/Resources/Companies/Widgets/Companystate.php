<?php

namespace App\Filament\Resources\Companies\Widgets;

use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Companystate extends StatsOverviewWidget
{
    use InteractsWithRecord;

    protected function getStats(): array
    {
        dd($this->getRecord());

        return [
            Stat::make('دائن', 66),
            Stat::make('مدين', 66),
            Stat::make('الصافي', 66),
        ];
    }
}
