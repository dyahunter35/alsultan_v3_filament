<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Companies\Widgets\CurrencyWidget;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Widgets;
use App\Services\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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
            Action::make('refresh')
                ->label(__('customer.actions.refresh.label'))
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {

                    app(CustomerService::class)->updateCustomerBalance($this->record);

                    Notification::make()
                        ->title(__('customer.notifications.refresh_success.title'))
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widgets\Companystate::make(['record', $this->record]),
            //Widgets\CustomerFinancialLedgerWidget::class,
        ];
    }
}
