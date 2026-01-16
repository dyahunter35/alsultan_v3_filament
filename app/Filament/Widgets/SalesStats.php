<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $dateFilter = function () {};

        if (auth()->user()->hasRole('super_admin')) {
            $dateFilter = function ($q) {
                $q->where('branch_id', Filament::getTenant()->id);

            };
        }

        // إحصائيات هذا الشهر
        $query = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where($dateFilter);

        $thisMonthTotal = $query->sum('total');

        $thisMonthOrders = $query->count();

        // الإحصائيات الكلية
        $overallTotal = Order::where($dateFilter)->sum('total');

        return [
            Stat::make('إجمالي الإيرادات (هذا الشهر)', number_format($thisMonthTotal) . ' ' . 'SDG')
                ->description('إجمالي مبيعات الشهر الحالي')
                ->color('success'),
            Stat::make('عدد الطلبات (هذا الشهر)', $thisMonthOrders)
                ->description('إجمالي عدد الطلبات في الشهر الحالي')
                ->color('info'),
            Stat::make('إجمالي الإيرادات (الكلي)', number_format($overallTotal) . ' ' . 'SDG')
                ->description('إجمالي المبيعات منذ البداية')
                ->color('warning'),
        ];
    }
}
