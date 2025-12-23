<?php

namespace App\Filament\Pages\Reports;

use App\Models\Truck;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ProductPricing extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'تسعير المنتجات (بيان الشحنة)';
    protected string $view = 'filament.pages.reports.product-pricing';

    public $truck_id;

    // المدخلات العامة
    public $exchange_rate = 52.4;
    public $customs_sdg = 17093746;
    public $transport_cost = 71000;

    // تم التغيير: مصفوفة لتخزين نسبة الربح لكل منتج بناءً على الـ ID الخاص به
    // Format: [cargo_id => percentage]
    public $profit_percents = [];

    public function mount()
    {
        $this->truck_id = request()->query('truck_id') ?? Truck::first()?->id;

        // تعبئة نسب الأرباح الافتراضية (4%) لكل الأصناف عند التحميل
        if ($this->truck_id) {
            $truck = Truck::with('cargos')->find($this->truck_id);
            if ($truck) {
                foreach ($truck->cargos as $cargo) {
                    $this->profit_percents[$cargo->id] = 4; // القيمة الافتراضية
                }
            }
        }
    }

    public function getReportDataProperty()
    {
        $truck = Truck::with('cargos.product')->find($this->truck_id);

        if (!$truck) return null;

        $cargos = $truck->cargos;

        // الحسابات العامة
        $total_weight_tons = $cargos->sum(fn($item) => ($item->quantity * $item->unit_quantity) / 1000);
        $customs_egp = $this->exchange_rate > 0 ? ($this->customs_sdg / $this->exchange_rate) : 0;

        $rows = $cargos->map(function ($item, $index) use ($total_weight_tons, $customs_egp) {

            $weight_ton = ($item->quantity * $item->unit_quantity) / 1000;
            $weight_ratio = $total_weight_tons > 0 ? ($weight_ton / $total_weight_tons) : 0;
            $base_total_egp = $weight_ton * $item->unit_price;

            $item_customs_cost = $customs_egp * $weight_ratio;
            $item_transport_cost = $this->transport_cost * $weight_ratio;
            $total_cost = $base_total_egp + $item_customs_cost + $item_transport_cost;

            // جلب نسبة الربح الخاصة بهذا الصف من المصفوفة، أو استخدام 0 إذا لم توجد
            $current_profit_percent = (float) ($this->profit_percents[$item->id] ?? 0);

            // حساب الأرباح بناءً على النسبة الخاصة بهذا الصف
            $profit_value = $total_cost * ($current_profit_percent / 100);

            $selling_price_egp_total = $total_cost + $profit_value;
            $package_price_egp = $item->quantity > 0 ? ($selling_price_egp_total / $item->quantity) : 0;
            $package_price_sdg = $package_price_egp * $this->exchange_rate;
            $ton_price_sdg = $weight_ton > 0 ? (($selling_price_egp_total * $this->exchange_rate) / $weight_ton) : 0;

            return (object) [
                'cargo_id' => $item->id, // مهم جداً للربط في العرض
                'index' => $index + 1,
                'product_name' => $item->product->name ?? 'منتج',
                'size' => $item->size,
                'unit_weight' => $item->unit_quantity,
                'quantity' => $item->quantity,
                'weight_ton' => $weight_ton,
                'unit_price' => $item->unit_price,
                'base_total_egp' => $base_total_egp,
                'transport_cost' => $item_transport_cost,
                'customs_cost' => $item_customs_cost,
                'total_cost' => $total_cost,
                'profit_percent' => $current_profit_percent, // النسبة الحالية
                'profit_value' => $profit_value,
                'selling_price_egp' => $selling_price_egp_total,
                'package_price_egp' => $package_price_egp,
                'package_price_sdg' => $package_price_sdg,
                'ton_price_sdg' => $ton_price_sdg,
            ];
        });

        return [
            'truck' => $truck,
            'rows' => $rows,
            'totals' => [
                'weight' => $total_weight_tons,
                'quantity' => $cargos->sum('quantity'),
                'base_egp' => $rows->sum('base_total_egp'),
                'transport' => $rows->sum('transport_cost'),
                'customs' => $rows->sum('customs_cost'),
                'total_cost' => $rows->sum('total_cost'),
                'profit' => $rows->sum('profit_value'),
                'selling_egp' => $rows->sum('selling_price_egp'),
            ],
            'customs_egp_total' => $customs_egp
        ];
    }
}
