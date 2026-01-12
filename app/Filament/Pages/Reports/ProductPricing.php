<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\Truck;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ProductPricing extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 37;

    protected static ?string $title = 'تسعير المنتجات (بيان الشحنة والشركات)';

    protected string $view = 'filament.pages.reports.product-pricing';

    #[Url('truck')]
    public $truck_id;

    #[Url('company')]
    public $company_id;

    public $_company;

    #[Url()]
    public $date_range;

    public $exchange_rate = 52.4;

    public $currency_name;

    // مصفوفة نسب الأرباح مرتبطة بـ ID الشحنة (cargo_id)
    public $profit_percents = [];

    public $profit_percent = 1;

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Section::make('خيارات العرض')
                ->schema([
                    Schemas\Components\Grid::make(4)->schema([
                        Forms\Components\Select::make('truck_id')
                            ->label('عرض شاحنة محددة')
                            ->options(Truck::query()->latest()->get()->mapWithKeys(fn ($t) => [$t->id => "شحنة رقم : ($t->id)"]))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->company_id = null),

                        Forms\Components\Select::make('company_id')
                            ->label('عرض تقرير شركة (جميع شاحناتها)')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->truck_id = null),

                        Forms\Components\TextInput::make('exchange_rate')
                            ->label('سعر الصرف')
                            ->numeric()
                            ->step(0.01)
                            ->reactive(),

                        DateRangePicker::make('date_range')
                            ->label('النطاق الزمني')
                            ->visible(fn () => $this->company_id)
                            ->disableClear(false)
                            ->live()
                            // التعديل الثاني: استخدام منطق afterStateUpdated بدقة أكبر
                            ->afterStateUpdated(function ($state) {
                                $this->date_range = $state;
                                $this->loadInitialData();
                            }),
                    ]),
                ])->collapsible(),
        ];
    }

    public function updatedDateRange()
    {
        $this->loadInitialData();
    }

    public function mount()
    {
        $this->loadInitialData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['truck_id', 'company_id', 'date_range'])) {
            $this->loadInitialData();
        }
    }

    /**
     * تجهيز دالة الفلترة بناءً على النطاق الزمني المحدد
     */
    protected function prepareDateFilter(): ?\Closure
    {
        if (! $this->date_range) {
            return null;
        }

        $dates = explode(' - ', $this->date_range);
        if (count($dates) !== 2) {
            return null;
        }

        try {
            $fromDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $toDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();

            return fn ($query) => $query->whereBetween('created_at', [$fromDate, $toDate]);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function loadInitialData()
    {
        $trucks = collect();
        $dateFilter = $this->prepareDateFilter();

        if ($this->truck_id) {

            $truck = Truck::with('cargos')->find($this->truck_id);
            $this->_company = $truck?->companyId;
            $trucks->push($truck);
        } elseif ($this->company_id) {
            $company = Company::find($this->company_id);
            $this->_company = $company;

            if ($company) {
                // تطبيق الفلتر على العلاقات المباشرة لضمان تطابق البيانات
                $trucks = $company->trucksAsCompany()
                    ->when($dateFilter, $dateFilter)
                    ->with('cargos')
                    ->get()
                    ->merge(
                        $company->trucksAsContractor()
                            ->when($dateFilter, $dateFilter)
                            ->with('cargos')
                            ->get()
                    );
            }
        }
        $this->exchange_rate = $this->_company?->currency?->exchange_rate;
        $this->currency_name = $this->_company?->currency?->name;

        foreach ($trucks->filter() as $truck) {
            foreach ($truck->cargos as $cargo) {
                if (! isset($this->profit_percents[$cargo->id])) {
                    $this->profit_percents[$cargo->id] = $this->profit_percent;
                }
            }
        }
    }

    public function getReportDataProperty(): ?Collection
    {
        $trucksList = collect();
        $dateFilter = $this->prepareDateFilter();

        if ($this->truck_id) {
            $truck = Truck::with(['cargos.product', 'expenses'])->find($this->truck_id);
            if ($truck) {
                $trucksList->push($truck);
            }
        } elseif ($this->company_id) {
            $company = Company::find($this->company_id);
            if ($company) {
                // جلب الشاحنات المفلترة مع كامل بياناتها للتقرير
                $trucksList = $company->trucksAsCompany()
                    ->with(['cargos.product', 'expenses'])
                    ->when($dateFilter, $dateFilter)
                    ->get()
                    ->merge(
                        $company->trucksAsContractor()
                            ->with(['cargos.product', 'expenses'])
                            ->when($dateFilter, $dateFilter)
                            ->get()
                    );
            }
        }

        if ($trucksList->isEmpty()) {
            return null;
        }

        return $trucksList->map(fn ($truck) => $this->calculateTruckData($truck));
    }

    /*  private function calculateTruckData($truck)
    {
        $exchange = (float) $this->exchange_rate ?: 1;
        $total_customs_sdg = $truck->expenses->sum('total_amount');
        $total_transport = $truck->truck_fare_sum;

        $customs_egp = $total_customs_sdg / $exchange;
        $cargos = $truck->cargos;
        $total_weight_tons = $cargos->sum(fn($item) => ($item->quantity * $item->unit_quantity) / 1000);

        $rows = $cargos->map(function ($item, $index) use ($total_weight_tons, $customs_egp, $total_transport, $exchange) {
            $weight_ton = ($item->quantity * $item->unit_quantity) / 1000;
            $weight_ratio = $total_weight_tons > 0 ? ($weight_ton / $total_weight_tons) : 0;

            $base_total_egp = $weight_ton * $item->unit_price;
            $item_customs_cost = $customs_egp * $weight_ratio;
            $item_transport_cost = $total_transport * $weight_ratio;
            $total_cost = $base_total_egp + $item_customs_cost + $item_transport_cost;

            $profit_percent = (float) ($this->profit_percents[$item->id] ?? 1);
            $profit_value = $total_cost * ($profit_percent / 100);

            $selling_egp = $total_cost + $profit_value;
            $package_egp = $item->quantity > 0 ? ($selling_egp / $item->quantity) : 0;
            $package_sdg = $package_egp * $exchange;
            $ton_sdg = $weight_ton > 0 ? (($selling_egp * $exchange) / $weight_ton) : 0;

            return (object) [
                'cargo_id' => $item->id,
                'index' => $index + 1,
                'product_name' => $item->product->name ?? 'منتج غير معروف',
                'size' => $item->size,
                'unit_weight' => $item->unit_quantity,
                'quantity' => $item->quantity,
                'weight_ton' => $weight_ton,
                'unit_price' => $item->unit_price,
                'base_total_egp' => $base_total_egp,
                'transport_cost' => $item_transport_cost,
                'customs_cost' => $item_customs_cost,
                'total_cost' => $total_cost,
                'profit_percent' => $profit_percent,
                'profit_value' => $profit_value,
                'selling_price_egp' => $selling_egp,
                'package_price_egp' => $package_egp,
                'package_price_sdg' => $package_sdg,
                'ton_price_sdg' => $ton_sdg,
            ];
        });

        return [
            'truck' => $truck,
            'rows' => $rows,
            'customs_egp_total' => $customs_egp,
            'totals' => [
                'quantity' => $cargos->sum('quantity'),
                'weight' => $total_weight_tons,
                'base_egp' => $rows->sum('base_total_egp'),
                'transport' => $rows->sum('transport_cost'),
                'customs' => $rows->sum('customs_cost'),
                'total_cost' => $rows->sum('total_cost'),
                'profit' => $rows->sum('profit_value'),
                'selling_egp' => $rows->sum('selling_price_egp'),
            ]
        ];
    } */

    private function calculateTruckData($truck)
    {
        $exchange = (float) $this->exchange_rate ?: 1;

        // جلب إجمالي المنصرفات (بالسوداني) ونولون الشاحنة (بالعملة الأجنبية)
        $total_customs_sdg = $truck->expenses->sum('total_amount');
        $total_transport = $truck->truck_fare_sum;

        // تحويل المنصرفات إلى العملة الأجنبية لتوزيعها على الأصناف
        $customs_foreign = $total_customs_sdg / $exchange;

        $cargos = $truck->cargos;

        // حساب إجمالي الأطنان (يدوي أو محسوب)
        $total_weight_tons = $cargos->sum(
            fn ($item) => $item->ton_weight // > 0 ? $item->ton_weight : ($item->weight * $item->unit_quantity) / 1000000
        );

        $rows = $cargos->map(function ($item, $index) use ($total_weight_tons, $customs_foreign, $total_transport, $exchange) {

            $weight_ton = $item->ton_weight; // > 0 ? $item->ton_weight : ($item->weight * $item->unit_quantity) / 1000000;
            $weight_ratio = $total_weight_tons > 0 ? ($weight_ton / $total_weight_tons) : 0;

            // التكلفة بالعملة الأجنبية (EGP أو غيرها)
            $base_total_foreign = $item->ton_price * $item->weight_ton; // $weight_ton * $item->unit_price;
            $item_customs_cost = $customs_foreign * $weight_ratio;
            $item_transport_cost = $total_transport * $weight_ratio;
            $total_cost_foreign = $base_total_foreign + $item_customs_cost + $item_transport_cost;

            // الربح
            $profit_percent = (float) ($this->profit_percents[$item->id] ?? 1);
            $profit_value = $total_cost_foreign * ($profit_percent / 100);

            // سعر البيع بالعملة الأجنبية
            $selling_foreign = $total_cost_foreign + $profit_value;

            // التسعير النهائي (بالسوداني وبالعملة الأجنبية)
            $package_foreign = $item->quantity > 0 ? ($selling_foreign / $item->quantity) : 0;
            $package_sdg = $package_foreign * $exchange;

            $ton_sdg = $weight_ton > 0 ? (($selling_foreign * $exchange) / $weight_ton) : 0;
            $ton_foreign = $weight_ton > 0 ? ($selling_foreign / $weight_ton) : 0;

            return (object) [
                'cargo_id' => $item->id,
                'index' => $index + 1,
                'product_name' => $item->product->name ?? 'منتج غير معروف',
                'size' => $item->size,
                'unit_weight' => $item->weight,
                'quantity' => $item->quantity,
                'unit_quantity' => number_format($item->unit_quantity),
                'weight_ton' => $weight_ton,
                'unit_price' => $item->unit_price,
                'ton_price' => $item->ton_price,
                'base_total_foreign' => $base_total_foreign,
                'transport_cost' => $item_transport_cost,
                'customs_cost' => $item_customs_cost,
                'total_cost' => $total_cost_foreign,
                'profit_percent' => $profit_percent,
                'profit_value' => $profit_value,
                'selling_price_foreign' => $selling_foreign,
                'package_price_foreign' => $package_foreign,
                'package_price_sdg' => $package_sdg,
                'ton_price_sdg' => $ton_sdg,
                'ton_price_foreign' => $ton_foreign,
            ];
        });

        return [
            'truck' => $truck,
            'rows' => $rows,
            'totals' => [
                'quantity' => $cargos->sum('quantity'),
                'weight' => $total_weight_tons,
                'base_foreign' => $rows->sum('base_total_foreign'),
                'transport' => $rows->sum('transport_cost'),
                'customs' => $rows->sum('customs_cost'),
                'total_cost' => $rows->sum('total_cost'),
                'profit' => $rows->sum('profit_value'),
                'selling_foreign' => $rows->sum('selling_price_foreign'),
            ],
        ];
    }
}
