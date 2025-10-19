<?php

namespace App\Filament\Resources\Customers\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions;
use Filament\Actions\Action;
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
                    $servies = new \App\Services\CustomerService();
                    $servies->updateCustomersBalance();

                    Notification::make()
                        ->title(__('customer.notifications.refresh_success.title'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
