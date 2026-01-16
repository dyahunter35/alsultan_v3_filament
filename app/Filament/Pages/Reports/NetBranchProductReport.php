<?php

namespace App\Filament\Pages\Reports;

use App\Enums\TruckState;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Scopes\IsVisibleScope;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class NetBranchProductReport extends Page implements HasForms
{
    use HasReport, InteractsWithForms;

    protected static ?int $navigationSort = 39;
    protected string $view = 'filament.pages.reports.net_branch_product_report';
    protected static ?string $navigationLabel = 'تقرير جرد الفروع التراكمي';

    #[Url] public ?int $branchId = null;
    #[Url] public ?int $productId = null;

    // البيانات المعالجة للعرض
    public Collection $branches;
    public Collection $reportData;
    public array $footerTotals = [];

    public function mount(): void
    {
        $this->form->fill([
            'branchId' => $this->branchId,
            'productId' => $this->productId,
        ]);
        $this->loadData();
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(3)->schema([
                Select::make('branchId')
                    ->label('المخزن / الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->placeholder('كافة الفروع')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Select::make('productId')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->placeholder('كافة المنتجات')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }

    /**
     * منطق معالجة البيانات وتحويلها (Logic Logic)
     */
    public function loadData(): void
    {
        // 1. تحديد الفروع التي ستظهر كأعمدة
        $this->branches = Branch::when($this->branchId, fn($q) => $q->where('id', $this->branchId))->get();

        // 2. جلب المنتجات مع بيانات الـ Pivot بكفاءة
        $products = Product::query()
            ->withOutGlobalScope(IsVisibleScope::class)
            ->with([
                    'branches' => function ($query) {
                        if ($this->branchId) {
                            $query->where('branches.id', $this->branchId);
                        }
                    }
                ])
            ->when($this->productId, fn($q) => $q->where('id', $this->productId))
            ->get();

        // 3. استخدام Map لتحويل المنتجات إلى كائنات بيانات بسيطة
        $this->reportData = $products->map(function ($product) {
            $balances = [];
            foreach ($this->branches as $branch) {
                $branchData = $product->branches->firstWhere('id', $branch->id);
                $balances[$branch->id] = $branchData?->pivot->total_quantity ?? 0;
            }

            // --- حسابات الشاحنات بناءً على الحالات المطلوبة ---
            // نقوم بفلترة حركات الشاحنات المرتبطة بهذا المنتج فقط
            $truckQuantities = \App\Models\TruckCargo::where('product_id', $product->id)
                ->whereHas('truck', function ($q) {
                    $q->whereIn('truck_status', [TruckState::barn->value, TruckState::port->value]);
                })
                ->get();

            return (object) [
                'name' => $product->name,
                'balances' => $balances,
                // كمية الحظيرة
                'barn_qty' => $truckQuantities->where('truck.truck_status', TruckState::barn->value)->sum('quantity'),
                // كمية الميناء
                'port_qty' => $truckQuantities->where('truck.truck_status', TruckState::port->value)->sum('quantity'),
                //'row_total' => array_sum($balances),
            ];
        });

        // 4. حساب إجمالي الأعمدة للـ Footer
        $this->calculateFooterTotals();
    }

    /* private function calculateFooterTotals(): void
    {
        $totals = [];
        foreach ($this->branches as $branch) {
            $totals[$branch->id] = $this->reportData->sum(fn($row) => $row->balances[$branch->id]);
        }
        $totals['grand_total'] = $this->reportData->sum('row_total');

        $this->footerTotals = $totals;
    } */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('تحديث الكميات')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    app(InventoryService::class)->updateAllBranches();
                    Notification::make()->title('تم تحديث المخزون')->success()->send();
                    $this->loadData();
                }),
            Action::make('print')
                ->label('طباعة')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(fn() => $this->js('window.print()')),
        ];
    }
}