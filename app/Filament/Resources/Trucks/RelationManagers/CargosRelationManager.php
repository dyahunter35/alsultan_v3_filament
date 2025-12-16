<?php

namespace App\Filament\Resources\Trucks\RelationManagers;

use App\Enums\TruckType;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Pages\Concerns\HasRelationManager;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CargosRelationManager extends RelationManager
{
    use HasRelationManager;
    protected static string $relationship = 'cargos';

    public function form(Schema $schema): Schema
    {
        self::translateConfigureForm();
        return $schema
            ->components([
                /* Select::make('type')
                    ->options(TruckType::class)
                    ->required(), */

                Hidden::make('type')
                    ->default(TruckType::Outer->value),

                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

                TextInput::make('size')
                    ->default(null),
                DecimalInput::make('unit_quantity')
                    ->required()
                    ->live(onBlur: true)
                    ->helperText('الكميات بالوحدة وليس الطرد')
                    ->numeric(),
                DecimalInput::make('quantity')
                    ->required()
                    ->live(onBlur: true)
                    ->numeric(),

                DecimalInput::make('weight')
                    ->numeric()
                    ->live(onBlur: true)
                    ->default(null),

                DecimalInput::make('unit_price')
                    ->numeric()
                    ->live(onBlur: true)
                    ->default(null),

            ]);
    }

    public function table(Table $table): Table
    {
        self::translateConfigureTable();
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                /* TextColumn::make('type')
                    ->badge(), */

                TextColumn::make('product.name')

                    ->sortable(),
                TextColumn::make('unit_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('real_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('size')
                    ->searchable(),

                TextColumn::make('unit_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note')
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
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
