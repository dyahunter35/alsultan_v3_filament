<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class OrdersReport extends Page
{
    use HasReport;

    protected string $view = 'filament.pages.reports.orders-report';

    protected static ?int $navigationSort = 38;

    #[Url()]
    public $date;

    public $summary = [];

    public $orders = [];

    public function mount()
    {
        // dd(static::getReportData(), self::getReportKey());
        if (empty($this->date)) {
            $this->date = now()->format('Y-m-d');
        }
        $this->loadData();
    }

    public function updatedDate()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $day = Carbon::parse($this->date);

        $query = Order::whereDate('created_at', $day);

        $totalSales = $query->sum('total');
        $countOrders = $query->count();
        $discounts = $query->sum('discount') ?? 0;

        $avgSale = $countOrders > 0 ? $totalSales / $countOrders : 0;

        $this->summary = [
            'total_sales' => $totalSales,
            'orders_count' => $countOrders,
            'avg_sale' => $avgSale,
            'discounts' => $discounts,
        ];

        $this->orders = $query->orderBy('id', 'desc')->get();
    }
}
