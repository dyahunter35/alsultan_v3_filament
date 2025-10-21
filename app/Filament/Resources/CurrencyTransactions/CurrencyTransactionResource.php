<?php

namespace App\Filament\Resources\CurrencyTransactions;

use App\Enums\ExpenseGroup;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphField;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\CurrencyTransactions\Pages\ManageCurrencyTransactions;
use App\Models\CurrencyTransaction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyTransactionResource extends Resource
{
    use HasResource;
    protected static ?string $model = CurrencyTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        self::translateConfigureForm();
        return $schema
            ->components([
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),

                MorphSelect::make('party')
                    ->models([
                        'company' => \App\Models\Company::class,
                        'customer' => fn() => \App\Models\Customer::where('permanent', ExpenseGroup::DEBTORS->value)->get(),
                    ]),
                Hidden::make('party_type')
                    ->required(),
                Hidden::make('party_id')
                    ->required(),
                DecimalInput::make('amount')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function ($set, $get, $state) {
                            $price = $get('rate') ?? 0;
                            $amount = $get('amount') ?? 0;
                            $set('total', ($amount * $price));
                        }
                    )
                    ->numeric(),
                DecimalInput::make('rate')
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function ($set, $get, $state) {
                            $price = $get('rate') ?? 0;
                            $amount = $get('amount') ?? 0;
                            $set('total', ($amount * $price));
                        }
                    )
                    ->default(1),
                DecimalInput::make('total')
                    ->required()
                    ->numeric(),

                Select::make('type')
                    ->options(['add' => 'Add', 'deduct' => 'Deduct'])
                    ->default('add')
                    ->required(),
                TextInput::make('note')
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        self::translateConfigureTable();
        return $table
            ->columns([
                TextColumn::make('currency.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('party.name')
                    ->searchable(),

                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencyTransactions::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
