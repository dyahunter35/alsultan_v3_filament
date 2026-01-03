<?php

namespace App\Filament\Resources\InnerTrucks\Schemas;

use App\Models\Truck;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TruckInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('driver_name'),
                TextEntry::make('driver_phone'),
                TextEntry::make('car_number'),
                TextEntry::make('pack_date')
                    ->date(),
                TextEntry::make('company_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('company')
                    ->placeholder('-'),
                TextEntry::make('from_type')
                    ->placeholder('-'),
                TextEntry::make('from_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('branch_to')
                    ->numeric(),
                TextEntry::make('arrive_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('truck_status')
                    ->badge(),
                /* TextEntry::make('type')
                    ->badge(), */
                IconEntry::make('is_converted')
                    ->boolean(),
                TextEntry::make('note')
                    ->placeholder('-'),
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('country')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('truck_model')
                    ->placeholder('-'),
                TextEntry::make('trip_days')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('diff_trip')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('agreed_duration')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('delay_day_value')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('truck_fare')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('delay_value')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Truck $record): bool => $record->trashed()),
            ]);
    }
}
