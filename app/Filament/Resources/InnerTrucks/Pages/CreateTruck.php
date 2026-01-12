<?php

namespace App\Filament\Resources\InnerTrucks\Pages;

use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTruck extends CreateRecord
{
    protected static string $resource = InnerTruckResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = \App\Models\Truck::generateTruckNumber(\App\Enums\TruckType::tryFrom(value: 'local'));

        return $data;
    }
}
