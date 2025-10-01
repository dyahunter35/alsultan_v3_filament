<?php

namespace App\Filament\Resources\Trucks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrucksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('driver_name')
                    ->searchable(),
                TextColumn::make('driver_phone')
                    ->searchable(),
                TextColumn::make('car_number')
                    ->searchable(),
                TextColumn::make('pack_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('company_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('from_type')
                    ->searchable(),
                TextColumn::make('from_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('branch_to')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('arrive_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('truck_status')
                    ->badge()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                IconColumn::make('is_converted')
                    ->boolean(),
                TextColumn::make('note')
                    ->searchable(),
                TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('country')
                    ->badge()
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('truck_model')
                    ->searchable(),
                TextColumn::make('trip_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('diff_trip')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('agreed_duration')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delay_day_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('truck_fare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delay_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
