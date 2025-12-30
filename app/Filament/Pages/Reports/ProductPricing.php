<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Truck;
use App\Models\Company;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;

class ProductPricing extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReport;

    protected static ?string $title = 'تسعير المنتجات (بيان الشحنة والشركات)';
    protected string $view = 'filament.pages.reports.product-pricing';

    #[Url('truck')]
    public $truck_id;

    #[Url('company')]
    public $company_id;

    // المعادل العام (سعر الصرف) - يؤثر على الجميع
    public $exchange_rate = 52.4;

    // مصفوفة نسب الأرباح مرتبطة بـ ID الشحنة (cargo_id)
    public $profit_percents = [];

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Section::make('خيارات العرض')
                ->schema([
                    Schemas\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('truck_id')
                            ->label('عرض شاحنة محددة')
                            ->options(Truck::query()->latest()->get()->mapWithKeys(fn($t) => [$t->id => "($t->id) {$t->driver_name}"]))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->company_id = null),

                        Forms\Components\Select::make('company_id')
                            ->label('عرض تقرير شركة (جميع شاحناتها)')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->truck_id = null),

                        Forms\Components\TextInput::make('exchange_rate')
                            ->label('سعر الصرف')
                            ->numeric()
                            ->step(0.01)
                            ->reactive(),
                    ]),
                ])->collapsible(),
        ];
    }

    public function mount()
    {
        $this->loadInitialData();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'truck_id' || $propertyName === 'company_id') {
            $this->loadInitialData();
        }
    }

    protected function loadInitialData()
    {
        $trucks = collect();
        if ($this->truck_id) {
            $trucks->push(Truck::with('cargos')->find($this->truck_id));
        } elseif ($this->company_id) {
            $company = Company::with(['trucksAsCompany.cargos', 'trucksAsContractor.cargos'])->find($this->company_id);
            if ($company) {
                $trucks = $company->trucksAsCompany->merge($company->trucksAsContractor);
            }
        }

        foreach ($trucks->filter() as $truck) {
            foreach ($truck->cargos as $cargo) {
                if (!isset($this->profit_percents[$cargo->id])) {
                    $this->profit_percents[$cargo->id] = 1; // النسبة الافتراضية
                }
            }
        }
    }

    public function getReportDataProperty(): ?Collection
    {
        $trucksList = collect();

        if ($this->truck_id) {
            $truck = Truck::with(['cargos.product', 'expenses'])->find($this->truck_id);
            if ($truck) $trucksList->push($truck);
        } elseif ($this->company_id) {
            $company = Company::find($this->company_id);
            if ($company) {
                $trucksList = $company->trucksAsCompany()->with(['cargos.product', 'expenses'])->get()
                    ->merge($company->trucksAsContractor()->with(['cargos.product', 'expenses'])->get());
            }
        }

        if ($trucksList->isEmpty()) return null;

        return $trucksList->map(fn($truck) => $this->calculateTruckData($truck));
    }

    private function calculateTruckData($truck)
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
    }
}
