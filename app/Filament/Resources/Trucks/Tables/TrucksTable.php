<?php

namespace App\Filament\Resources\Trucks\Tables;

use App\Enums\Country;
use App\Enums\TruckType;
use App\Models\Truck;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TrucksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Truck::where('type', TruckType::Outer))
            ->columns([

                Tables\Columns\TextColumn::make('driver_name')
                    ->getStateUsing(fn($record) =>  $record->driver_name . '<br>' . $record->driver_phone)->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('car_number')
                    ->getStateUsing(fn($record) =>  $record->truck_model . '<br>' . $record->car_number)->html()

                    ->searchable(),
                Tables\Columns\TextColumn::make('pack_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('truck_status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->badge()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('contractorInfo.name')
                    ->label(__('truck.fields.contractor_id.label'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('companyId.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toBranch.name')
                    ->label(__('truck.fields.to.label')),
                Tables\Columns\TextColumn::make('arrive_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_converted')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('toStore')
                    ->label(__('truck.filters.toStore.label'))
                    ->relationship('toBranch', 'name'),

                SelectFilter::make('country')
                    ->label(__('truck.filters.country.label'))
                    ->options(Country::class),

                DateRangeFilter::make('pack_date')
                    ->label(__('truck.filters.pack_date.label'))->opens(OpenDirection::RIGHT),
                DateRangeFilter::make('arrive_date')
                    ->label(__('truck.filters.arrive_date.label'))->opens(OpenDirection::RIGHT),

            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->groupedBulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
