<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Companies\Widgets\CurrencyWidget;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Widgets;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // Widgets\Companystate::make(['record', $this->record]),
            Widgets\CustomerFinanceOverview::class,
            CurrencyWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widgets\Companystate::make(['record', $this->record]),
            Widgets\CustomerFinancialLedgerWidget::class,
        ];
    }
}
