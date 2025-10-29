<?php

namespace App\Filament\Resources\CurrencyTransactions;

use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphField;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\CurrencyTransactions\Pages\ManageCurrencyTransactions;
use App\Models\CurrencyBalance;
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
use Filament\Forms\Components\ViewField;
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

                Select::make('type')
                    ->options(\App\Enums\CurrencyType::class)
                    ->default(CurrencyType::SEND)
                    ->live()
                    ->columnSpanFull()
                    ->required(),

                ViewField::make('payer_currencies')
                    ->label('Currencies')
                    ->view('filament.resources.customers.forms.customer-currencies-table')
                    ->reactive()
                    ->hidden(fn(callable $get) => empty($get('payer_currencies')))
                    ->columnSpanFull(),

                Select::make('payer_id')
                    ->options(fn() => \App\Models\Customer::where('permanent', ExpenseGroup::DEBTORS->value)->get()
                        ->mapWithKeys(fn(\App\Models\Customer $customer) => [
                            $customer->id => sprintf(
                                '%s (%s SDG)',
                                $customer->name,
                                number_format($customer->balance, 2)
                            )
                        ]))
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $currencies = CurrencyBalance::where('owner_type', \App\Models\Customer::class)
                            ->where('owner_id', $state)
                            ->get();

                        $set('payer_currencies', $currencies);
                    }),


                Hidden::make('payer_type')
                    ->default(\App\Models\Customer::class)
                    ->required(),

                MorphSelect::make('party')
                    ->models([
                        'company' => \App\Models\Company::class,
                        'customer' => fn() => \App\Models\Customer::where('permanent', ExpenseGroup::DEBTORS->value)->get(),
                    ])
                    ->live()
                    ->visible(fn(callable $get) =>  $get('type') === CurrencyType::SEND),
                Hidden::make('party_type')
                    ->required(fn(callable $get) =>  $get('type') === CurrencyType::SEND),

                Hidden::make('party_id')
                    ->required(fn(callable $get) =>  $get('type') === CurrencyType::SEND),


                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                DecimalInput::make('amount')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function ($set, $get, $state) {
                            //dd($get('type'));
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

                TextColumn::make('payer.name')
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
