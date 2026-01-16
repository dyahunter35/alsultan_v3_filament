<?php

namespace App\Filament\Pages\Reports;

use App\Enums\StockCase;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Scopes\IsVisibleScope;
use App\Services\InventoryService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;

class BranchProductStockReport extends Page implements HasForms
{
    use HasReport, InteractsWithForms;

    //protected static bool $isScopedToTenant = true;
    protected string $view = 'filament.pages.reports.branch-product-stock-report';

    #[Url] public ?int $productId = null;
    #[Url] public ?int $branchId = null;
    #[Url] public ?bool $withZero = null;

    // سنخزن البيانات المعالجة هنا
    public Collection $reportData;
    public $branch;

    public function mount(): void
    {
        $this->branchId = $this->branchId ?? Filament::getTenant()?->id;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->branch = Branch::find($this->branchId) ?? Filament::getTenant();

        // 1. جلب البيانات الخام مع العلاقات اللازمة فقط
        $rawProducts = Product::query()
            ->withOutGlobalScope(IsVisibleScope::class)
            ->with(['history' => fn($q) => $q->where('branch_id', $this->branchId)])
            ->when($this->productId, fn($q) => $q->where('id', $this->productId))
            ->get();

        // 2. استخدام Collection Map لفصل المنطق الحسابي
        $this->reportData = $rawProducts->map(function ($product) {
            $history = $product->history;

            $initial = $history->where('type', StockCase::Initial)->sum('quantity_change');
            $increase = $history->where('type', StockCase::Increase)->sum('quantity_change');
            $decrease = $history->where('type', StockCase::Decrease)->sum('quantity_change');
            $net = ($initial + $increase) - $decrease;
            return (object) [
                'id' => $product->id,
                'name' => $product->name,
                'initial' => $initial,
                'increase' => $increase,
                'decrease' => $decrease,
                'current_balance' => $net,
            ];
        });
    }

    // حساب الإجمالي العام للتقرير ليتم عرضه في الـ Footer
    /* public function getTotalInventoryProperty(): float
    {
        return $this->reportData->sum('current_balance');
    } */

    protected function getFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(3)->schema([
                Select::make('branchId')
                    ->label('المخزن المختار')
                    ->options(Branch::pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated($this->loadData()),
                Select::make('productId')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated($this->loadData()),

                ToggleButtons::make('withZero')
                    ->boolean('تضمين', 'عدم التضمين')
                    ->label('تضمين المنتجات الصفرية')
                    ->live()
                    ->inline()
                    ->afterStateUpdated($this->loadData())
            ]),
        ];
    }
}