<?php

namespace App\Filament\Clusters\Expanes\Resources\ExpenseTypes\Pages;

use App\Filament\Clusters\Expanes\Resources\ExpenseTypes\ExpenseTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExpenseTypes extends ManageRecords
{
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
