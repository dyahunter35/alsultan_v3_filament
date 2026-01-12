<?php

namespace App\Filament\Resources\InnerTrucks\Pages;

use App\Filament\Pages\Reports\TruckReport;
use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use App\Models\Truck;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTruck extends EditRecord
{
    protected static string $resource = InnerTruckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('report')
                ->label(__('truck.actions.report.label'))
                ->icon(__('truck.actions.report.icon'))
                ->action(fn (Truck $record) => redirect(TruckReport::getUrl(['truckId' => $record->id]))),

            // ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
