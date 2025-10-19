<?php

namespace App\Filament\Resources\Customers\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Customers\Widgets;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            //Widgets\Companystate::make(['record', $this->record]),
            Widgets\CustomerFinanceOverview::class,
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
            //Widgets\Companystate::make(['record', $this->record]),
            Widgets\CustomerFinancialLedgerWidget::class,
        ];
    }
}
