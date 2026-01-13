<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\CurrencyTransaction;
use App\Models\Truck;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class CompanyLedgerReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.company-ledger';

    protected static ?int $navigationSort = 33;

    #[Url()]
    public ?int $companyId = null;

    public ?Company $_company = null;

    #[Url()]
    public $startDate;

    #[Url()]
    public $endDate;

    // البيانات للمعالجة
    public $groups = [];

    public $summary = [
        'total_claims' => 0,
        'total_paid' => 0,
        'balance' => 0, // تأكد من وجود هذا المفتاح يدوياً
    ];

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Section::make()
                ->schema([
                    Schemas\Components\Grid::make(4)->schema([
                        Forms\Components\Select::make('companyId')
                            ->label('الشركة')
                            ->options(Company::query()->latest()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),
                        Forms\Components\DatePicker::make('startDate')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),
                        Forms\Components\DatePicker::make('endDate')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),
                    ]),
                ])->collapsible(),
        ];
    }

    public function mount(): void
    {
        $this->companyId = request()->query('companyId');
        $this->startDate = request()->query('startDate');
        $this->endDate = request()->query('endDate');

        if ($this->companyId) {
            $this->_company = Company::findOrFail($this->companyId);
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. جلب الشاحنات المرتبطة بالشركة (كشركة أو كمقاول)
        $trucks = Truck::with(['cargos.product'])
            ->where(function ($q) {
                $q->where('company_id', $this->companyId)
                    ->orWhere('contractor_id', $this->companyId);
            })
            // ->when($start && $end, function ($q) use ($start, $end) {
            //     $q->whereBetween('pack_date', [$start, $end]);
            // })
            // ->whereBetween('pack_date', [$start, $end])
            ->orderBy('pack_date')
            ->get();

        $allGroups = [];
        $totalClaims = 0; // إجمالي المطالبات
        $totalPayments = 0; // إجمالي المدفوعات

        foreach ($trucks as $truck) {
            // حساب إجمالي الشحنة من الـ Cargos

            $total_weight_tons = $truck->cargos->sum(
                fn($item) => $item->ton_weight // > 0 ? $item->ton_weight : ($item->weight * $item->unit_quantity) / 1000000
            );

            $cargos = $truck->cargos->map(function ($item, $index) use ($total_weight_tons) {
                $weight_ton = $item->ton_weight; // > 0 ? $item->ton_weight : ($item->weight * $item->unit_quantity) / 1000000;
                $weight_ratio = $total_weight_tons > 0 ? ($weight_ton / $total_weight_tons) : 0;

                // التكلفة بالعملة الأجنبية (EGP أو غيرها)
                $base_total_foreign = $item->ton_price * $weight_ton;

                return (object) [
                    'cargo_id' => $item->id,
                    'index' => $index + 1,
                    'product_name' => $item->product->name ?? 'منتج غير معروف',
                    'size' => $item->size,
                    'unit_weight' => $item->weight,
                    'quantity' => $item->quantity,
                    'unit_quantity' => $item->unit_quantity,
                    'weight_ton' => $weight_ton,
                    'unit_price' => $item->unit_price,
                    'ton_price' => $item->ton_price,
                    'base_total_foreign' => $base_total_foreign,
                ];
            });

            $cargoTotalValue = $cargos->sum('base_total_foreign');

            // جلب المعاملات المالية المرتبطة بهذه الشحنة تحديداً (إذا كنت تربطها بـ truck_id)
            // أو جلب المعاملات التي تمت في تاريخ الشحنة
            $payments = CurrencyTransaction::where('truck_id', $truck->id)->get();
            $paymentValue = $payments->sum('total');
            $allGroups[] = [
                'date' => $truck->pack_date,
                'truck_id' => $truck->id,
                'car_number' => $truck->car_number,
                'cargos' => $cargos,
                'payments' => $payments,
                'total_invoice' => $cargoTotalValue,
                'total_paid' => $paymentValue,
                'balance' => $cargoTotalValue - $paymentValue,
            ];

            $totalClaims += $cargoTotalValue;
            $totalPayments += $payments->sum('total');
        }

        $this->groups = $allGroups;
        $this->summary = [
            'total_claims' => $totalClaims,
            'total_paid' => $totalPayments,
            'balance' => $totalClaims - $totalPayments,
        ];
    }
}
