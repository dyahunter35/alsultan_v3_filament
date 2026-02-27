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
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class NetBranchProductReport extends Page implements HasForms
{
    use HasReport, InteractsWithForms;

    protected static ?int $navigationSort = 42;
    protected string $view = 'filament.pages.reports.net_branch_product_report';
    protected static ?string $navigationLabel = 'تقرير جرد الفروع التراكمي';

    #[Url] public $branchId = [];
    #[Url] public ?int $productId = null;
    #[Url] public bool $withZero = false; // القيمة الافتراضية عدم التضمين

    public Collection $branches;
    public Collection $reportData;

    public function mount(): void
    {
        $this->form->fill([
            'branchId' => $this->branchId,
            'productId' => $this->productId,
            'withZero' => $this->withZero,
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
                    ->live()
                    ->suffixAction(Action::make('clear')
                        ->color('danger')
                        ->icon(Heroicon::Trash)
                        ->action(fn() => [$this->branchId = [], $this->loadData()]))
                    ->multiple()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Select::make('productId')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                ToggleButtons::make('withZero')
                    ->label('المنتجات الصفرية')
                    ->options([
                            true => 'تضمين الكل',
                            false => 'إخفاء الصفرية تماماً',
                        ])
                    ->icons([true => 'heroicon-o-eye', false => 'heroicon-o-eye-slash'])
                    ->colors([true => 'success', false => 'gray'])
                    ->default(false)
                    ->live()
                    ->inline()
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }

    public function loadData(): void
    {
        $this->branches = Branch::when($this->branchId, fn($q) => $q->whereIn('id', $this->branchId))->get();

        $this->js("document.title = '{$this->getReportSubject()}'");

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

        $this->reportData = $products->map(function ($product) {
            $balances = [];
            foreach ($this->branches as $branch) {
                $branchData = $product->branches->firstWhere('id', $branch->id);
                $balances[$branch->id] = $branchData?->pivot->total_quantity ?? 0;
            }

            // جلب كميات الشاحنات (Cargo)
            $truckQuantities = \App\Models\TruckCargo::where('product_id', $product->id)
                ->whereHas('truck', function ($q) {
                    $q->whereIn('truck_status', [TruckState::barn->value, TruckState::port->value]);
                })
                ->get();

            $barnQty = $truckQuantities->where('truck.truck_status', TruckState::barn->value)->sum('quantity');
            $portQty = $truckQuantities->where('truck.truck_status', TruckState::port->value)->sum('quantity');
            $branchesTotal = array_sum($balances);

            return (object) [
                'name' => $product->name,
                'balances' => $balances,
                'barn_qty' => $barnQty,
                'port_qty' => $portQty,
                'branches_total' => $branchesTotal,
                // المجموع الشامل لكل شيء
                'grand_total' => $branchesTotal + $barnQty + $portQty,
            ];
        })
            // --- المنطق المطلوب هنا ---
            ->when(!$this->withZero, function ($collection) {
                return $collection->filter(function ($item) {
                    // اظهر المنتج إذا كان لديه رصيد في الفروع OR الحظيرة OR الميناء
                    return $item->branches_total != 0 || $item->barn_qty != 0 || $item->port_qty != 0;
                });
            });
    }

    public function updateQty()
    {
        app(InventoryService::class)->updateAllBranches();
        Notification::make()->title('تم تحديث المخزون')->success()->send();
        $this->loadData();
    }
}