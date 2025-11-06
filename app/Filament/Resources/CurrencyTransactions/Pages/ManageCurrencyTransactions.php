<?php

namespace App\Filament\Resources\CurrencyTransactions\Pages;

use App\Filament\Resources\CurrencyTransactions\CurrencyTransactionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Grid;

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
