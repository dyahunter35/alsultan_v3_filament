<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Services\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('refresh')
                ->label(__('customer.actions.refresh.label'))
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {

                    app(CustomerService::class)->updateCustomersBalance();

                    Notification::make()
                        ->title(__('customer.notifications.refresh_success.title'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
