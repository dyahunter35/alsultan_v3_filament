<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Trucks\TruckResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TruckRelationManager extends RelationManager
{
    protected static string $relationship = 'trucks';

    protected static ?string $relatedResource = TruckResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
               Actions\CreateAction::make(),
            ]);
    }
}
