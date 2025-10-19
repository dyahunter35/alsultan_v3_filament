<?php

namespace App\Filament\Resources\Supplyings\Pages;

use App\Filament\Resources\Supplyings\SupplyingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplyings extends ListRecords
{
    protected static string $resource = SupplyingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
