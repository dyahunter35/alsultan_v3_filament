<?php

namespace App\Filament\Pages;

use App\Enums\StockCase;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockHistory;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class InventoryReconciliation extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.inventory-reconciliation';

    #[Url] public ?int $branchId = null;
    public array $productIds = [];
    #[Url] public bool $showAllProducts = true;

    // هذا المتغير ضروري جداً لمقارنة الفرع ومنع المسح عند الفلترة
    public ?int $oldBranchId = null;

    public array $productsData = [];
    public array $actualQuantities = [];
    public ?Collection $products = null;

    public function mount(): void
    {
        // عند التحميل لأول مرة، نثبت الـ oldBranchId
        $this->oldBranchId = $this->branchId;
        $this->loadProducts();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)
                ->schema([
                        Select::make('branchId')
                            ->label('اختر الفرع')
                            ->options(auth()->user()->branch->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->columns(1)
                            ->afterStateUpdated(function ($state) {
                                $this->loadProducts();
                            }),

                        Select::make('productIds')
                            ->label('تصفية بالمنتج')
                            ->options(fn() => Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->live()
                            ->columnSpan(2)
                            ->visible(fn() => $this->branchId)
                            ->suffixAction(
                                Action::make('remove_all')
                                    ->label('إلغاء الكل')
                                    ->iconButton()
                                    ->icon('heroicon-o-x-circle')
                                    ->action(function () {
                                        $this->productIds = [];
                                        $this->loadProducts();
                                    })
                            )
                            ->afterStateUpdated(fn() => $this->loadProducts()),

                        ToggleButtons::make('showAllProducts')
                            ->label('نطاق العرض')
                            ->options([
                                    false => 'منتجات الفرع',
                                    true => 'كل المنتجات',
                                ])
                            ->default(false)
                            ->inline()
                            ->visible(fn() => $this->branchId)

                            ->grouped()
                            ->live()
                            ->afterStateUpdated(fn() => $this->loadProducts()),
                    ]),
        ];
    }

    public function loadProducts(): void
    {
        if (!$this->branchId) {
            $this->productsData = [];
            $this->actualQuantities = [];
            $this->oldBranchId = null;
            return;
        }

        // تصحيح منطق تصفير البيانات:
        // إذا تغير الفرع المختار، نقوم بتصفير المصفوفة.
        // إذا كان نفس الفرع وتغيرت الفلاتر الأخرى، نترك المصفوفة كما هي.
        if ($this->oldBranchId !== $this->branchId) {
            $this->actualQuantities = [];
            $this->oldBranchId = $this->branchId;
        }

        $query = Product::query()
            ->with(['branches' => fn($q) => $q->where('branches.id', $this->branchId)])
            ->when($this->productIds, fn($q) => $q->whereIn('id', $this->productIds));

        if (!$this->showAllProducts) {
            $query->whereHas('branches', fn($q) => $q->where('branches.id', $this->branchId));
        }

        $this->products = $query->get();
        $this->productsData = [];

        foreach ($this->products as $product) {
            $branchData = $product->branches->first();
            $sysQty = $branchData ? (float) ($branchData->pivot->total_quantity ?? 0) : 0;

            $this->productsData[] = [
                'id' => $product->id,
                'name' => $product->name,
                'system_quantity' => $sysQty,
            ];

            // الحارس (Guard): إذا كان المنتج له قيمة مخزنة مسبقاً في المصفوفة، لا نغيرها.
            // نضع القيمة الافتراضية فقط إذا كان المنتج يظهر لأول مرة في القائمة.
            if (!array_key_exists($product->id, $this->actualQuantities)) {
                $this->actualQuantities[$product->id] = $sysQty;
            }
        }
    }

    public function saveReconciliation(): void
    {
        if (!$this->branchId)
            return;

        DB::beginTransaction();
        try {
            $changesMade = false;

            foreach ($this->productsData as $p) {
                $pid = $p['id'];
                $sysQty = (float) $p['system_quantity'];

                // جلب ما كتبه المستخدم
                $actualInput = isset($this->actualQuantities[$pid]) && $this->actualQuantities[$pid] !== ''
                    ? (float) $this->actualQuantities[$pid]
                    : $sysQty;

                $diff = $actualInput - $sysQty;

                if (abs($diff) > 0.0001) {
                    StockHistory::create([
                        'product_id' => $pid,
                        'branch_id' => $this->branchId,
                        'type' => $diff > 0 ? StockCase::Increase : StockCase::Decrease,
                        'quantity_change' => abs($diff),
                        'new_quantity' => $actualInput,
                        'notes' => 'تسوية جردية (مطابقة مخزون)',
                        'user_id' => auth()->id() ?? 1,
                    ]);

                    $product = Product::find($pid);
                    $product->branches()->syncWithPivotValues(
                        [$this->branchId],
                        ['total_quantity' => $actualInput],
                        false
                    );

                    $changesMade = true;
                }
            }

            DB::commit();

            if ($changesMade) {
                app(InventoryService::class)->updateAllBranches();
                Notification::make()->title('تم حفظ التسوية بنجاح')->success()->send();

                // بعد الحفظ الناجح، نعيد تحميل البيانات لتحديث "كمية النظام"
                $this->loadProducts();
            } else {
                Notification::make()->title('لم يتم رصد أي تغييرات')->warning()->send();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('خطأ أثناء الحفظ')->body($e->getMessage())->danger()->send();
        }
    }

    public function updateQuantities(): void
    {
        app(InventoryService::class)->updateAllBranches();
        Notification::make()->title('تم التحديث')->success()->send();
        $this->loadProducts();
    }
}