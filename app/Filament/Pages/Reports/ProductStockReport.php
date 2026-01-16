<?php

namespace App\Filament\Pages\Reports;

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
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ProductStockReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 39;
    protected string $view = 'filament.resources.product-resource.pages.product-stock-report';
    protected static ?string $navigationLabel = 'تقرير مخزون المنتجات';

    #[Url] public ?int $branchId = null;
    #[Url] public ?int $productId = null;

    public $products;
    public $branches;
    public function mount(): void
    {
        $this->loadData();
    }
    /**
     * تعريف الفلاتر في هيدر الصفحة
     */
    protected function getFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(3)->schema([
                Select::make('branchId')
                    ->label('تصفية حسب الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->placeholder('جميع الفروع')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Select::make('productId')
                    ->label('تصفية حسب المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->placeholder('جميع المنتجات')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }

    /**
     * جلب البيانات وتحسين الأداء
     */
    public function loadData()
    {
        $filters = [$this->branchId, $this->productId];

        // 1. جلب الفروع (مع الفلترة إذا اختار المستخدم فرعاً محدداً)
        $this->branches = Branch::when($this->branchId ?? null, fn($query) => $query->where('id', $this->branchId))
            ->get();

        // 2. جلب المنتجات مع تحسين الاستعلام (Eager Loading)
        $this->products = Product::query()
            ->withOutGlobalScope(IsVisibleScope::class)
            // نستخدم eager loading للبيانات المرتبطة بالفروع لتقليل الاستعلامات
            ->with([
                'branches' =>
                    fn($query) => $query->where('branches.id', $this->branchId)

            ])
            // تصفية حسب منتج محدد
            ->when($this->productId ?? null, fn($query) => $query->where('id', $this->productId))

            ->get();
    }

    public function refreshQty()
    {
        app(InventoryService::class)->updateAllBranches();
        Notification::make()
            ->title('تم تحديث المخزون')
            ->success()
            ->send();
    }
}