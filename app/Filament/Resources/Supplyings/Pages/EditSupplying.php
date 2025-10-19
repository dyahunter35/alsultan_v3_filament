<?php

namespace App\Filament\Resources\Supplyings\Pages;

use App\Filament\Resources\Supplyings\SupplyingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplying extends EditRecord
{
    protected static string $resource = SupplyingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
