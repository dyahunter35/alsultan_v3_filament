<?php

namespace App\Filament\Resources\InnerTrucks;

use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\InnerTrucks\Pages\CreateTruck;
use App\Filament\Resources\InnerTrucks\Pages\EditTruck;
use App\Filament\Resources\InnerTrucks\Pages\ListTrucks;
use App\Filament\Resources\InnerTrucks\Pages\ViewTruck;
use App\Filament\Resources\InnerTrucks\RelationManagers\CargosRelationManager;
use App\Filament\Resources\InnerTrucks\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\InnerTrucks\RelationManagers\ShipmentExpenseRelationManager;
use App\Filament\Resources\InnerTrucks\RelationManagers\TaxExpensesRelationManager;
use App\Filament\Resources\InnerTrucks\Schemas\TruckForm;
use App\Filament\Resources\InnerTrucks\Schemas\TruckInfolist;
use App\Filament\Resources\InnerTrucks\Tables\TrucksTable;
use App\Models\Truck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InnerTruckResource extends Resource
{
    use HasResource;

    protected static ?string $model = Truck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'driver_name';

    protected static ?int $navigationSort = 15;

    public static function getLocalePath(): string
    {
        return 'truck';
    }

    public static function getPluralModelLabel(): string
    {
        return static::getLocale('navigation.inner.plural_label') ?? parent::getPluralModelLabel();
    }

    /**
     * Get model label with translation
     */
    public static function getModelLabel(): string
    {
        return static::getLocale('navigation.inner.model_label') ?? parent::getModelLabel();
    }

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
            CargosRelationManager::class,
            DocumentsRelationManager::class,
            // TaxExpensesRelationManager::class,
            ShipmentExpenseRelationManager::class,
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
