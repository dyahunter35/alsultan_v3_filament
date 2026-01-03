<?php

namespace App\Filament\Resources\Trucks\RelationManagers;

use App\Enums\TruckType;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Pages\Concerns\HasRelationManager;
use App\Models\Product;
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
                    ->options(
                        Product::get()
                            ->mapWithKeys(fn(Product $product) => [
                                $product->id => sprintf(
                                    '%s - %s (%s) ',
                                    $product->name,
                                    $product->category?->name,
                                    $product->unit?->name
                                ),
                            ])
                    )->preload()
                    ->searchable()
                    ->required(),

                TextInput::make('size')
                    ->default(null),
                DecimalInput::make('unit_quantity')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $unitQuantity = (float) str_replace(',', '', $get('unit_quantity') ?? 1); // وزن الوحدة (مثلاً 50 كجم)
                        $weight = (float) str_replace(',', '', $get('weight') ?? 1); // الكمية (مثلاً 200 جوال)

                        // الحسبة الافتراضية: (الكمية × وزن الوحدة) / 1000 للحصول على الأطنان
                        $tonWeight = ($weight * $unitQuantity) / 1000000;

                        // تحديث الحقل في الواجهة
                        $set('ton_weight', number_format($tonWeight, 2, '.', ''));
                    }),
                DecimalInput::make('quantity')
                    ->required(),

                DecimalInput::make('weight')
                    ->default(null)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $unitQuantity = (float) str_replace(',', '', $get('unit_quantity') ?? 1); // وزن الوحدة (مثلاً 50 كجم)
                        $weight = (float) str_replace(',', '', $get('weight') ?? 1); // الكمية (مثلاً 200 جوال)

                        // الحسبة الافتراضية: (الكمية × وزن الوحدة) / 1000 للحصول على الأطنان
                        $tonWeight = ($weight * $unitQuantity) / 1000000;

                        // تحديث الحقل في الواجهة
                        $set('ton_weight', number_format($tonWeight, 2, '.', ''));
                    }),

                DecimalInput::make('ton_weight')
                    ->default(null),

                DecimalInput::make('unit_price')
                    ->live(onBlur: true)
                    ->default(null),

                TextInput::make('note')
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
                //AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                //DissociateAction::make(),
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
