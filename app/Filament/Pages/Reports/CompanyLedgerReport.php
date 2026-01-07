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

    #[Url()]
    public ?int $companyId = null;

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
                            ->afterStateUpdated(fn () => $this->loadData()),
                        Forms\Components\DatePicker::make('startDate')
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->loadData()),
                        Forms\Components\DatePicker::make('endDate')
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->loadData()),
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
            $cargoTotalValue = $truck->cargos->sum(function ($cargo) {
                $weight = $cargo->weight ?? ($cargo->quantity * $cargo->unit_quantity / 1000);

                return $weight * $cargo->unit_price;
            });

            // جلب المعاملات المالية المرتبطة بهذه الشحنة تحديداً (إذا كنت تربطها بـ truck_id)
            // أو جلب المعاملات التي تمت في تاريخ الشحنة
            $payments = CurrencyTransaction::
            // where('party_id', $this->companyId)
                // ->where('party_type', operator: Company::class)
                // ->whereDate('created_at', $truck->pack_date)
                where('truck_id', $truck->id)
                    ->get();
            $paymentValue = $payments->sum('total');
            $allGroups[] = [
                'date' => $truck->pack_date,
                'truck_id' => $truck->id,
                'car_number' => $truck->car_number,
                'cargos' => $truck->cargos,
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
