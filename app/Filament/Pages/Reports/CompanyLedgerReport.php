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
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class CompanyLedgerReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.company-ledger';
    protected static ?int $navigationSort = 51;

    #[Url()]
    public ?int $companyId = null;
    public ?Company $_company = null;

    #[Url()]
    public $date_range = null;

    public $groups = []; // لعرض تفاصيل الشاحنات
    public $report_lines = []; // لكشف الحساب التراكمي
    public $opening_balance = 0;
    public $summary = ['total_debit' => 0, 'total_credit' => 0, 'final_balance' => 0];

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                Forms\Components\Select::make('companyId')
                    ->label('الشركة / المقاول')
                    ->options(Company::query()->latest()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData()),

                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }

    public function mount(): void
    {
        if ($this->companyId) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        if (!$this->companyId)
            return;
        $this->_company = Company::find($this->companyId);

        // معالجة التاريخ
        [$start, $end] = function_exists('parseDateRange') ? parseDateRange($this->date_range) : [null, null];

        // 1. حساب الرصيد السابق (قبل فترة التقرير)
        $this->opening_balance = 0;
        if ($start) {
            $prev_trucks_ids = Truck::where(fn($q) => $q->where('company_id', $this->companyId)->orWhere('contractor_id', $this->companyId))
                ->where('pack_date', '<', $start)->pluck('id');

            $prev_debit = DB::table('cargos')->whereIn('truck_id', $prev_trucks_ids)->sum(DB::raw('ton_price * ton_weight'));

            $prev_credit = CurrencyTransaction::where('party_id', $this->companyId)
                ->where('party_type', Company::class)
                ->where('created_at', '<', $start)->sum('total');

            $this->opening_balance = $prev_debit - $prev_credit;
        }

        // 2. جلب الشاحنات (الفواتير)
        $trucks = Truck::with(['cargos.product'])
            ->where(fn($q) => $q->where('company_id', $this->companyId)->orWhere('contractor_id', $this->companyId))
            ->when($start && $end, fn($q) => $q->whereBetween('pack_date', [$start, $end]))
            ->orderBy('pack_date')->get();

        $this->groups = [];
        $lines = collect();

        foreach ($trucks as $truck) {
            $invoice_total = $truck->cargos->sum(fn($c) => $c->ton_price * $c->ton_weight);
            $this->groups[] = [
                'date' => $truck->pack_date,
                'truck_id' => $truck->id,
                'car_number' => $truck->car_number,
                'cargos' => $truck->cargos,
                'total_invoice' => $invoice_total,
            ];
            // إضافة كحركة مدين لكشف الحساب
            $lines->push(['date' => $truck->pack_date, 'reference' => "شحنة #{$truck->id} ({$truck->car_number})", 'debit' => $invoice_total, 'credit' => 0]);
        }

        // 3. جلب كافة المدفوعات (مستقلة عن الشاحنات)
        $payments = $this->_company->currencyTransactions()
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get();

        foreach ($payments as $payment) {
            $lines->push([
                'date' => $payment->created_at->format('Y-m-d'),
                'reference' => "سند سداد مالي #{$payment->id}",
                'debit' => 0,
                'credit' => $payment->total
            ]);
        }

        // 4. ترتيب كشف الحساب التراكمي
        $sortedLines = $lines->sortBy('date')->values();
        $runningBalance = $this->opening_balance;

        $this->report_lines = $sortedLines->map(function ($l) use (&$runningBalance) {
            $runningBalance += ($l['debit'] - $l['credit']);
            $l['balance'] = $runningBalance;
            return $l;
        })->toArray();

        $this->summary = [
            'total_debit' => $sortedLines->sum('debit'),
            'total_credit' => $sortedLines->sum('credit'),
            'final_balance' => $runningBalance
        ];
    }
}