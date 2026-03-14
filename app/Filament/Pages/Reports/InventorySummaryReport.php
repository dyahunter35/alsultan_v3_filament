<?php

namespace App\Filament\Pages\Reports;

use App\Enums\TruckState;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\TruckCargo;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\DelegateService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class InventorySummaryReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.inventory-summary-report';

    // --- Currency & Conversion ---
    /** @var array<int, float> rate per currency_id */
    public array $exchangeRates = [];
    public ?int $targetCurrencyId = null;

    public array $customRates = [
        'goods' => [],
        'customers' => [],
        'delegates' => [],
        'companies' => [],
    ];

    // --- Report Sections ---
    public array $goodsCosts = [];   // Section 1
    public array $customers = [];   // Section 2
    public array $delegates = [];   // Section 3
    public array $companies = [];   // Section 4

    // --- Totals ---
    public float $goodsCostsTotal = 0;
    public float $customersTotal = 0;
    public float $delegatesTotal = 0;
    public float $companiesTotal = 0;
    public float $grandTotal = 0;

    public string $targetCurrencyName = '';

    public function mount(): void
    {
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $this->exchangeRates[$currency->id] = (float) $currency->exchange_rate;
        }
        $this->targetCurrencyId = $currencies->first()?->id;
        $this->targetCurrencyName = $currencies->first()?->name ?? '';

        $this->form->fill([
            'targetCurrencyId' => $this->targetCurrencyId,
        ]);

        $this->loadData();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('targetCurrencyId')
                ->label('تمييز العملة')
                ->options(Currency::pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->targetCurrencyId = (int) $state;
                    $this->targetCurrencyName = Currency::find($state)?->name ?? '';
                    $this->customRates = [
                        'goods' => [],
                        'customers' => [],
                        'delegates' => [],
                        'companies' => [],
                    ];
                    $this->loadData();
                }),
        ]);
    }

    public function updateRate(int $currencyId, float $value): void
    {
        $this->exchangeRates[$currencyId] = $value;
        $this->customRates = [
            'goods' => [],
            'customers' => [],
            'delegates' => [],
            'companies' => [],
        ];
        $this->loadData();
    }

    public function updateCustomRate(string $section, int $index, $value): void
    {
        $this->customRates[$section][$index] = (float) $value;
        $this->loadData();
    }

    public function loadData(): void
    {
        $rate = $this->exchangeRates[$this->targetCurrencyId] ?? 1;

        $this->loadGoodsCosts($rate);
        $this->loadCustomers($rate);
        $this->loadDelegates($rate);
        $this->loadCompanies($rate);

        $this->grandTotal = $this->goodsCostsTotal
            + $this->customersTotal
            + $this->delegatesTotal
            + $this->companiesTotal;

        $this->js("document.title = '{$this->getReportSubject()}'");
    }

    /** Livewire action alias used by the view's wire:click="update" button */
    public function updatePalance(): void
    {
        DB::beginTransaction();
        try {
            app(DelegateService::class)->calculateAllUserBalances();
            app(CustomerService::class)->updateCustomersBalance();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make('')
                ->body('حدث خطأ أثناء تحديث الحساب')
                ->icon(Heroicon::UserPlus)
                ->send();
        }

        Notification::make('')
            ->body('تم تحديث الحساب بنحاح')
            ->icon(Heroicon::UserPlus)
            ->send();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Section 1 — تكاليف البضاعة
    // ─────────────────────────────────────────────────────────────────────────
    private function loadGoodsCosts(float $defaultRate): void
    {
        // 1a. مخزون الفروع (المخازن) — branch_product pivot
        $warehouseValue = (float) TruckCargo::whereHas(
            'truck',
            fn($q) =>
            $q->where('truck_status', TruckState::reach->value)
        )
            ->sum(DB::raw('COALESCE(base_total_foreign, ton_weight * ton_price)'));

        // 1b. البضاعة في الطريق — TruckCargo where truck.truck_status = OnWay
        $onWayValue = (float) TruckCargo::whereHas(
            'truck',
            fn($q) =>
            $q->where('truck_status', TruckState::OnWay->value)
        )->sum(DB::raw('COALESCE(base_total_foreign, ton_weight * ton_price)'));

        // 1c. بضاعة الميناء
        $portValue = (float) TruckCargo::whereHas(
            'truck',
            fn($q) =>
            $q->where('truck_status', TruckState::port->value)
        )->sum(DB::raw('COALESCE(base_total_foreign, ton_weight * ton_price)'));

        // 1d. بضاعة الحظيرة
        $barnValue = (float) TruckCargo::whereHas(
            'truck',
            fn($q) =>
            $q->where('truck_status', TruckState::barn->value)
        )->sum(DB::raw('COALESCE(base_total_foreign, ton_weight * ton_price)'));

        // 1e. مصاريف الشحن المرتبطة بالشاحنات الفعّالة (expenses linked to active trucks cargo)
        $truckExpenses = (float) DB::table('expenses')
            ->whereNotNull('truck_id')
            ->whereNull('deleted_at')
            ->sum('amount');

        $items = [
            ['label' => 'تكلفة بضاعة المخازن', 'value' => $warehouseValue],
            ['label' => 'تكلفة البضاعة علي الطريق', 'value' => $onWayValue],
            ['label' => 'تكلفة بضاعة الميناء', 'value' => $portValue],
            ['label' => 'تكلفة بضاعة الحظيرة', 'value' => $barnValue],
            ['label' => 'مصاريف الشحن المرتبطة', 'value' => $truckExpenses],
        ];

        $totalGoodsCostEquivalent = 0;
        $this->goodsCosts = [];

        foreach ($items as $i => $item) {
            $rate = $this->customRates['goods'][$i] ?? $defaultRate;
            $equiv = $rate > 0 ? $item['value'] / $rate : 0;
            $totalGoodsCostEquivalent += $equiv;

            $this->goodsCosts[] = [
                'label' => $item['label'],
                'value' => $item['value'],
                'rate' => $rate,
                'equivalent' => $equiv,
            ];
        }

        $this->goodsCostsTotal = $totalGoodsCostEquivalent;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Section 2 — رصيد العملاء
    // ─────────────────────────────────────────────────────────────────────────
    private function loadCustomers(float $defaultRate): void
    {
        $customers = Customer::withSum('sales as total_sales', 'total')
            ->sale()
            ->withSum('supplyings as total_deposits', 'total_amount')
            ->get();

        $totalSales = $customers->sum('total_sales');
        $totalDeposits = $customers->sum('total_deposits');

        $balance = $totalSales - $totalDeposits;
        $rate = $this->customRates['customers'][0] ?? $defaultRate;
        $equivalent = $rate > 0 ? $balance / $rate : 0;

        $this->customers = [
            [
                'label' => 'رصيد العملاء',
                'sales' => $totalSales,
                'deposits' => $totalDeposits,
                'balance' => $balance,
                'rate' => $rate,
                'equivalent' => $equivalent,
            ],
        ];

        $this->customersTotal = $equivalent;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Section 3 — رصيد المناديب (الخزنة)
    // ─────────────────────────────────────────────────────────────────────────
    private function loadDelegates(float $defaultRate): void
    {
        // Using the User::sales() scope which filters ROLE_SALES + is_valut
        // scopeSales returns a pluck, so we rebuild query ourselves using same criteria
        $delegates = User::role(User::ROLE_SALES)->orWhere('is_valut', true)->get();

        $totalBalanceEquivalent = 0;
        $rows = [];

        foreach ($delegates as $i => $delegate) {
            $deposits = (float) $delegate->total_received;    // expensesAsBeneficiary
            $expenses = (float) $delegate->total_paid;        // expensesAsPayer
            $balance = (float) $delegate->net_balance;

            $rate = $this->customRates['delegates'][$i] ?? $defaultRate;
            $equiv = $rate > 0 ? $balance / $rate : 0;
            $totalBalanceEquivalent += $equiv;

            $rows[] = [
                'label' => $delegate->name,
                'deposits' => $deposits,
                'expenses' => $expenses,
                'balance' => $balance,
                'rate' => $rate,
                'equivalent' => $equiv,
            ];
        }

        $this->delegates = $rows;
        $this->delegatesTotal = $totalBalanceEquivalent;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Section 4 — رصيد الشركات والمصانع
    // ─────────────────────────────────────────────────────────────────────────
    private function loadCompanies(float $defaultRate): void
    {
        // Companies: currency balances (foreign) from CurrencyBalance
        $companyCurrencyBalance = (float) DB::table('currency_balances')
            ->where('owner_type', Company::class)
            ->sum('total_in_sdg');

        // Currency customers (conversion clients)
        $currencyCustomerBalance = (float) DB::table('currency_balances')
            ->where('owner_type', Customer::class)
            ->sum('total_in_sdg');

        $rate0 = $this->customRates['companies'][0] ?? $defaultRate;
        $companyEquiv = $rate0 > 0 ? $companyCurrencyBalance / $rate0 : 0;

        $rate1 = $this->customRates['companies'][1] ?? $defaultRate;
        $customerEquiv = $rate1 > 0 ? $currencyCustomerBalance / $rate1 : 0;

        $this->companies = [
            [
                'label' => 'رصيد الشركات والمصانع',
                'claims' => $companyCurrencyBalance,
                'payments' => 0,
                'balance' => $companyCurrencyBalance,
                'rate' => $rate0,
                'equivalent' => $companyEquiv,
            ],
            [
                'label' => 'رصيد عملاء التحويلات',
                'claims' => $currencyCustomerBalance,
                'payments' => 0,
                'balance' => $currencyCustomerBalance,
                'rate' => $rate1,
                'equivalent' => $customerEquiv,
            ],
        ];

        $this->companiesTotal = $companyEquiv + $customerEquiv;
    }

    public function getReportSubject(): string
    {
        return 'تقرير الجرد الشامل';
    }

    public function getTitle(): string
    {
        return 'تقرير الجرد الشامل';
    }
}
