<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Order;
use App\Models\Branch; // تأكد من استيراد موديل الفرع
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Schemas;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class OrdersReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected string $view = 'filament.pages.reports.orders-report';
    protected static ?int $navigationSort = 38;

    #[Url]
    public $date_range;

    #[Url]
    public $branch_id; // إضافة معرف الفرع للـ URL

    public $summary = [];
    public $orders;

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(2)->schema([
                // حقل اختيار الفرع
                Forms\Components\Select::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->placeholder('كل الفروع') // لإظهار الكل عند عدم الاختيار
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData()),

                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    ->disableClear(false)
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->date_range = $state;
                        $this->loadData();
                    }),
            ]),
        ];
    }

    public function mount(): void
    {
        $this->orders = collect();

        if (empty($this->date_range)) {
            $this->date_range = now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y');
        }

        $this->form->fill([
            'date_range' => $this->date_range,
            'branch_id' => $this->branch_id,
        ]);

        $this->loadData();
    }

    protected function parseDateRange(): array
    {
        if (! $this->date_range) {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        $dates = explode(' - ', $this->date_range);
        if (count($dates) !== 2) {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        try {
            $from = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
            return [$from, $to];
        } catch (\Exception $e) {
            return [now()->startOfDay(), now()->endOfDay()];
        }
    }

    public function loadData(): void
    {
        [$from, $to] = $this->parseDateRange();

        $query = Order::with(['items.product', 'branch']) // تم إضافة الـ branch للـ Eager Loading
            ->whereBetween('created_at', [$from, $to])
            // منطق الفلترة للفرع: إذا كان $branch_id موجوداً يتم الفلترة، وإلا يتم جلب الكل
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id));

        $totalSales = (float) $query->sum('total');
        $countOrders = $query->count();
        $discounts = (float) $query->sum('discount') ?? 0;

        $this->summary = [
            'total_sales' => $totalSales,
            'orders_count' => $countOrders,
            'avg_sale' => $countOrders > 0 ? $totalSales / $countOrders : 0,
            'discounts' => $discounts,
        ];

        $this->orders = $query->orderBy('created_at', 'desc')->get();
    }
}
