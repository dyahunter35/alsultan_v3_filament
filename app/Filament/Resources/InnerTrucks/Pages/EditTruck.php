<?php

namespace App\Filament\Resources\InnerTrucks\Pages;

use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTruck extends EditRecord
{
    protected static string $resource = InnerTruckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
