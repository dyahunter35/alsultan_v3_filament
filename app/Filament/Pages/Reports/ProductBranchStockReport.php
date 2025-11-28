<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use Filament\Actions\Action;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Scopes\IsVisibleScope;
use App\Services\InventoryService;
use Filament\Pages\Page;
use Filament\Actions;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

class ProductBranchStockReport extends Page
{
    use HasReport;

    protected static bool $isScopedToTenant = true;

    public function getReportParameters(): array
    {
        return  ['b' =>  Filament::getTenant()->name];
    }

    // --- NAVIGATION ---
    /* public function getTitle(): string | Htmlable
    {
        return __('report.product_branch_stock_report.heading',);
    }

    public function getHeading(): string | Htmlable
    {
        return __('report.product_branch_stock_report.heading', ['b' =>  Filament::getTenant()->name]);
    }*/

    public static function getNavigationLabel(): string
    {
        return __('report.product_branch_stock_report.heading', ['b' =>  Filament::getTenant()->name]);
    }

    // protected static string $view = 'filament.resources.product-resource.pages.product-stock-report';
    protected string $view = 'filament.resources.product-resource.branch-report';
    // protected static string $view = 'welcome';


    /**
     * إعداد البيانات التي سيتم تمريرها إلى ملف العرض (Blade).
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        // جلب كل الفروع لإنشاء أعمدة الجدول بشكل ديناميكي
        $branch = Filament::getTenant();

        // جلب كل المنتجات مع علاقاتها بالفروع
        // نستخدم withSum لحساب الإجمالي بكفاءة عالية
        $products = Product::query()
            ->with('history')
            ->withOutGlobalScope(IsVisibleScope::class)
            // ->with('branches') // لجلب بيانات pivot لكل فرع
            ->get();

        return [
            'products' => $products,
            'branch' => $branch,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label(__('product.actions.print.label'))
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function () {
                    $this->js('window.print()');
                }),

            Action::make('refresh')
                ->label(__('product.actions.refresh.label'))
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {
                    $servies = new InventoryService;
                    $servies->updateAllBranches();
                }),
        ];
    }
}
