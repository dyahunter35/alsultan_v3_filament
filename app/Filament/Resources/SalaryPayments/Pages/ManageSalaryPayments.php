<?php

namespace App\Filament\Resources\SalaryPayments\Pages;

use App\Filament\Resources\SalaryPayments\SalaryPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSalaryPayments extends ManageRecords
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
