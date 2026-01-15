<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Branch; // تأكد من استيراد موديل الفرع
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
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

    #[Url] public $date_range;
    #[Url] public $customerId;
    #[Url] public $representative_id;
    #[Url] public $branch_id;
    public $summary = [];
    public $orders;

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('customerId')
                    ->label('العميل')
                    ->options(Customer::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn($state) => [$this->customerId = $state, $this->loadData()]),

                Forms\Components\Select::make('representative_id')
                    ->label('المندوب')
                    ->options(User::sales())
                    ->placeholder('كل المناديب')
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->representative_id = $state, $this->loadData()]),
                // حقل اختيار الفرع
                Forms\Components\Select::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->placeholder('كل الفروع') // لإظهار الكل عند عدم الاختيار
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->branch_id = $state, $this->loadData()]),

                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    //->disableClear(false)
                    ->live()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->date_range = null, $this->loadData()])
                    )
                    ->afterStateUpdated(
                        fn($state) => [$this->date_range = $state, $this->loadData()]
                    ),
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

    public function loadData(): void
    {
        [$from, $to] = parseDateRange($this->date_range);

        $query = Order::with(['items.product', 'branch']) // تم إضافة الـ branch للـ Eager Loading
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))            // منطق الفلترة للفرع: إذا كان $branch_id موجوداً يتم الفلترة، وإلا يتم جلب الكل
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
            ->when($this->representative_id, fn($q) => $q->where('representative_id', $this->representative_id));

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
