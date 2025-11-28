<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Pages\Concerns\HasReport;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\StockHistories\StockHistoryResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Pages\Page;

class SingleStockReport extends Page
{
    use InteractsWithRecord;
    use HasReport;

    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.resources.products.pages.single-stock-report';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
