<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\DelegateService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_palances')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->label(__('customer.actions.refresh.label'))
                ->action(function () {
                    $users = User::valut()->get();
                    foreach ($users as $user) {
                        app(DelegateService::class)->calculateUserBalances($user);
                    }
                    Notification::make('success')
                        ->body(__('customer.notifications.refresh_success.title'))
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
