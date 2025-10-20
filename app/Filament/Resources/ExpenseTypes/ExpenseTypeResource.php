<?php

namespace App\Filament\Resources\ExpenseTypes;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\ExpenseTypes\Pages\ManageExpenseTypes;
use App\Models\ExpenseType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpenseTypeResource extends Resource
{
    use HasResource;

    protected static ?string $model = ExpenseType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        self::translateConfigureForm();
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                Select::make('group')
                    ->options(ExpenseGroup::class)
                    ->default(null),

            ]);
    }

    public static function table(Table $table): Table
    {
        self::translateConfigureTable();
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('group')
                    ->badge()
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenseTypes::route('/'),
        ];
    }
}
