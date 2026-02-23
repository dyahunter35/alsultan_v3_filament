<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Filament\Resources\Trucks\TruckResource;
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

    public $combined_records = []; // البيانات المدمجة للجدول العلوي
    public $report_lines = [];     // كشف الحساب التفصيلي
    public $opening_balance = 0;
    public $summary = ['total_debit' => 0, 'total_credit' => 0, 'final_balance' => 0];

    public function mount()
    {
        if ($this->companyId) {
            $this->_company = Company::find($this->companyId);
            $this->loadData();
        }
    }
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

    public function loadData(): void
    {
        if (!$this->companyId)
            return;

        $this->_company = Company::find($this->companyId);

        // استخراج تواريخ البداية والنهاية (بفرض وجود Helper Function لديك)
        $start = null;
        $end = null;
        if ($this->date_range) {
            $dates = explode(' - ', $this->date_range);
            $start = $dates[0] ?? null;
            $end = $dates[1] ?? null;
        }

        // 1. حساب الرصيد الافتتاحي (قبل تاريخ البداية)
        $this->opening_balance = 0;
        if ($start) {
            $prev_trucks_ids = Truck::where(fn($q) => $q->where('company_id', $this->companyId)->orWhere('contractor_id', $this->companyId))
                ->where('pack_date', '<', $start)->pluck('id');

            $prev_debit = DB::table('cargos')->whereIn('truck_id', $prev_trucks_ids)->sum(DB::raw('ton_price * ton_weight'));

            $prev_credit = CurrencyTransaction::where('party_id', $this->companyId)
                ->where('party_type', Company::class)
                ->where('created_at', '<', $start)
                ->sum('total');

            $this->opening_balance = $prev_debit - $prev_credit;
        }

        // 2. جلب الشحنات
        $trucks = Truck::with(['cargos.product'])
            ->where(fn($q) => $q->where('company_id', $this->companyId)->orWhere('contractor_id', $this->companyId))
            ->when($start && $end, fn($q) => $q->whereBetween('pack_date', [$start, $end]))
            ->get();

        // 3. جلب السندات (Currency Transactions)
        $payments = CurrencyTransaction::where('party_id', $this->companyId)
            ->where('party_type', Company::class)
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get();

        // 4. دمج البيانات وترتيبها زمنياً
        $combined = collect();

        foreach ($trucks as $t) {
            $combined->push([
                'type' => 'truck',
                'date' => $t->pack_date,
                'id' => $t->id,
                'cargos' => $t->cargos,
                'total' => $t->cargos->sum(fn($c) => $c->ton_price * $c->ton_weight)
            ]);
        }

        foreach ($payments as $p) {
            $combined->push([
                'type' => 'payment',
                'date' => $p->created_at->format('Y-m-d'),
                'id' => $p->id,
                'description' => $p->note ?? "سند سداد #{$p->id}",
                'amount' => $p->total
            ]);
        }

        $this->combined_records = $combined->sortBy('date')->values()->toArray();

        // 5. بناء كشف الحساب التراكمي
        $running = $this->opening_balance;
        $this->report_lines = [];

        foreach ($this->combined_records as $record) {
            $debit = $record['type'] === 'truck' ? $record['total'] : 0;
            $credit = $record['type'] === 'payment' ? $record['amount'] : 0;
            $running += ($debit - $credit);

            $this->report_lines[] = [
                'id' => $record['id'],
                'date' => $record['date'],
                'ref' => $record['type'] === 'truck' ? "شحنة #{$record['id']}" : $record['description'],
                'debit' => $debit,
                'type' => $record['type'],
                'credit' => $credit,
                'balance' => $running
            ];
        }

        $this->summary = [
            'total_debit' => collect($this->report_lines)->sum('debit'),
            'total_credit' => collect($this->report_lines)->sum('credit'),
            'final_balance' => $running
        ];
    }
    public function viewTruck($id)
    {
        // open in new tab use filament action
        return \Filament\Actions\Action::make('viewTruck')
            ->label('عرض')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->url(TruckResource::getUrl('edit', ['record' => $id]))
            ->openUrlInNewTab();
    }
}