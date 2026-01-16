<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockHistory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class ProductHistoryReport extends Page implements HasForms
{
    use HasReport, InteractsWithForms;

    protected static ?int $navigationSort = 40;
    protected string $view = 'filament.pages.reports.product-history-report';
    protected static ?string $navigationLabel = 'تقرير حركة المنتج';

    #[Url] public ?int $branchId = null;
    #[Url] public ?int $productId = null;
    #[Url] public ?string $dateFrom = null;
    #[Url] public ?string $dateTo = null;

    public Collection $reportData;

    public function mount(): void
    {
        $this->form->fill([
            'branchId' => $this->branchId,
            'productId' => $this->productId,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->reportData = StockHistory::query()
            ->with(['branch', 'product', 'user', 'truck'])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->when($this->productId, fn($q) => $q->where('product_id', $this->productId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->get();
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(4)->schema([
                Select::make('branchId')
                    ->label('الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Select::make('productId')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                DatePicker::make('dateFrom')
                    ->label('من تاريخ')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                DatePicker::make('dateTo')
                    ->label('إلى تاريخ')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }
}
