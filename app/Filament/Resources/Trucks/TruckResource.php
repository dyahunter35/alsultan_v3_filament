<?php

namespace App\Filament\Resources\Trucks;

use App\Enums\TruckState;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\Trucks\Pages\CreateTruck;
use App\Filament\Resources\Trucks\Pages\EditTruck;
use App\Filament\Resources\Trucks\Pages\ListTrucks;
use App\Filament\Resources\Trucks\Pages\ViewTruck;
use App\Filament\Resources\Trucks\RelationManagers\CargosRelationManager;
use App\Filament\Resources\Trucks\RelationManagers\CustomExpensesRelationManager;
use App\Filament\Resources\Trucks\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Trucks\RelationManagers\ShipmentExpenseRelationManager;
use App\Filament\Resources\Trucks\Schemas\TruckForm;
use App\Filament\Resources\Trucks\Schemas\TruckInfolist;
use App\Filament\Resources\Trucks\Tables\TrucksTable;
use App\Models\Truck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TruckResource extends Resource
{
    use HasResource;

    protected static ?string $model = Truck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 12;

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
            ShipmentExpenseRelationManager::class,
            // CustomExpensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrucks::route('/'),
            'create' => CreateTruck::route('/create'),
            // 'view' => ViewTruck::route('/{record}'),
            'edit' => EditTruck::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'id'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            // __('truck.fields.code.label') => $record->code,
            __('truck.fields.pack_date.label') => $record->pack_date,
            __('order.fields.created_at.label') => $record->created_at,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->out();
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;

        return (string) $modelClass::out()->where('truck_status', TruckState::OnWay)->count();
    }
}
