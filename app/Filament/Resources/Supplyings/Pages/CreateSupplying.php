<?php

namespace App\Filament\Resources\Supplyings\Pages;

use App\Filament\Resources\Supplyings\SupplyingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplying extends CreateRecord
{
    protected static string $resource = SupplyingResource::class;


    /* protected function mutateFormDataBeforeCreate(array $data): array
    {
        dd($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        dd($data);
    } */
}
