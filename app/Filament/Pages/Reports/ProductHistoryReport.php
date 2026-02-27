<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockHistory;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ProductHistoryReport extends Page implements HasForms
{
    use HasReport, InteractsWithForms;

    protected static ?int $navigationSort = 40;
    protected string $view = 'filament.pages.reports.product-history-report';

    public ?array $_label = null;
    #[Url] public ?int $branchId = null;
    #[Url] public ?int $productId = null;
    #[Url] public ?string $date_range = null;

    public function getReportSubject(): ?string
    {
        return $this->_label['name'] ?? '';
    }
    public Collection $reportData;

    public function mount(): void
    {
        $this->form->fill([
            'branchId' => $this->branchId,
            'productId' => $this->productId,
            'date_range' => $this->date_range,
        ]);
        $this->loadData();
    }

    public function loadData(): void
    {
        if ($this->productId) {
            $product = Product::find($this->productId);


            $qty = ($this->branchId) ? $product->branches()->where('branch_id', $this->branchId)->first()?->pivot->total_quantity ?? 0 : number_format($product->total_stock, 1);

            $this->_label = [
                'name' => $this->getNavigationLabel() . ' : ' . $product->name,
                'qty' => $qty,
            ];
        } else {
            $this->_label = [
                'name' => $this->getNavigationLabel(),
                'qty' => null
            ];
        }

        [$from, $to] = parseDateRange($this->date_range);

        $this->reportData = StockHistory::query()
            ->with(['branch', 'product', 'user', 'truck'])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->when($this->productId, fn($q) => $q->where('product_id', $this->productId))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->get();

        $this->js("document.title = '{$this->getPrintTitle()}'");

    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(4)->schema([

                Select::make('productId')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->preload()

                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Select::make('branchId')
                    ->label('الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    //->disableClear(false)
                    ->live()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->date_range = null, $this->loadData()])
                    )
                    ->afterStateUpdated(
                        fn($state) => [$this->date_range = $state, $this->loadData()]
                    ),
            ]),
        ];
    }

    public function updateQty()
    {
        app(InventoryService::class)->updateAllBranches();
        Notification::make()->title('تم تحديث المخزون')->success()->send();
        $this->loadData();
    }
}
