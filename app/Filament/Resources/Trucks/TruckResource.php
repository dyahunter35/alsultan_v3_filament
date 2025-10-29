<?php

namespace App\Filament\Resources\Trucks;

use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\Trucks\Pages\CreateTruck;
use App\Filament\Resources\Trucks\Pages\EditTruck;
use App\Filament\Resources\Trucks\Pages\ListTrucks;
use App\Filament\Resources\Trucks\Pages\ViewTruck;
use App\Filament\Resources\Trucks\RelationManagers\CustomExpensesRelationManager;
use App\Filament\Resources\Trucks\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Trucks\RelationManagers\TaxExpensesRelationManager;
use App\Filament\Resources\Trucks\Schemas\TruckForm;
use App\Filament\Resources\Trucks\Schemas\TruckInfolist;
use App\Filament\Resources\Trucks\Tables\TrucksTable;
use App\Models\Truck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TruckResource extends Resource
{
    use HasResource;

    protected static ?string $model = Truck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        static::translateConfigureForm();
        return TruckForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        static::translateConfigureInfolist();
        return TruckInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        static::translateConfigureTable();
        return TrucksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            TaxExpensesRelationManager::class,
            CustomExpensesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrucks::route('/'),
            'create' => CreateTruck::route('/create'),
            'view' => ViewTruck::route('/{record}'),
            'edit' => EditTruck::route('/{record}/edit'),
        ];
    }
}
