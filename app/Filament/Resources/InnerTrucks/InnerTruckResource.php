<?php

namespace App\Filament\Resources\InnerTrucks;

use App\Enums\TruckState;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InnerTruckResource extends Resource
{
    use HasResource;

    protected static ?string $model = Truck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

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
            //__('truck.fields.code.label') => $record->code,
            __('truck.fields.pack_date.label') => $record->pack_date,
            __('order.fields.created_at.label') => $record->created_at,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->local();
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;

        return (string) $modelClass::local()->where('truck_status', TruckState::OnWay)->count();
    }
}
