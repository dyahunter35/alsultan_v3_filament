<?php

namespace App\Filament\Resources\CurrencyTransactions\Pages;

use App\Filament\Resources\CurrencyTransactions\CurrencyTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCurrencyTransactions extends ManageRecords
{
    protected static string $resource = CurrencyTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
