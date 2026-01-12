<?php

namespace App\Filament\Resources\Trucks\Pages;

use App\Filament\Resources\Trucks\TruckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTruck extends CreateRecord
{
    protected static string $resource = TruckResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = \App\Models\Truck::generateTruckNumber(\App\Enums\TruckType::tryFrom('outer'));

        return $data;
    }
}
