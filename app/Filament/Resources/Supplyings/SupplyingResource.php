<?php

namespace App\Filament\Resources\Supplyings;

use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\Supplyings\Pages\CreateSupplying;
use App\Filament\Resources\Supplyings\Pages\EditSupplying;
use App\Filament\Resources\Supplyings\Pages\ListSupplyings;
use App\Filament\Resources\Supplyings\Schemas\SupplyingForm;
use App\Filament\Resources\Supplyings\Tables\SupplyingsTable;
use App\Models\Supplying;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplyingResource extends Resource
{
    use HasResource;

    protected static ?string $model = Supplying::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?int $navigationSort = 3;

    // protected static ?string $recordTitleAttribute = 'customer_id';

    public static function form(Schema $schema): Schema
    {
        self::translateConfigureForm();

        return SupplyingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        self::translateConfigureTable();

        return SupplyingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplyings::route('/'),
            'create' => CreateSupplying::route('/create'),
            'edit' => EditSupplying::route('/{record}/edit'),
        ];
    }
}
