<?php

namespace App\Filament\Resources\InnerTrucks\Pages;

use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTruck extends CreateRecord
{
    protected static string $resource = InnerTruckResource::class;
}
