<?php

namespace App\Filament\Resources\Trucks\Pages;

use App\Filament\Resources\Trucks\TruckResource;
use App\Filament\Resources\Trucks\Widgets\TruckFinanceOverview;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTruck extends ViewRecord
{
    protected static string $resource = TruckResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TruckFinanceOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
