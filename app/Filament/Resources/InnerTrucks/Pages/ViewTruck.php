<?php

namespace App\Filament\Resources\InnerTrucks\Pages;

use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use App\Filament\Resources\InnerTrucks\Widgets\TruckFinanceOverview;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTruck extends ViewRecord
{
    protected static string $resource = InnerTruckResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            //TruckFinanceOverview::class,
        ];
    }


    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
