<?php

namespace App\Filament\Resources\CurrencyTransactions;

use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\CurrencyTransactions\Pages\ManageCurrencyTransactions;
use App\Models\Company;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyTransactionResource extends Resource
{
    use HasResource;

    protected static ?string $model = CurrencyTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTopRightOnSquare;

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        self::translateConfigureForm();

        return $schema
            ->components(self::formSchema());
    }

    public static function table(Table $table): Table
    {
        self::translateConfigureTable();

        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('currency.name')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payer.name')
                    ->searchable(),

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
                SelectFilter::make('type')
                    ->options(CurrencyType::class),
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

    public static function formSchema($type = CurrencyType::SEND): array
    {
        return [

            Select::make('type')
                ->options(\App\Enums\CurrencyType::class)
                ->default($type)
                ->live()
                ->columnSpanFull()
                ->afterStateUpdated(function (callable $set, $state) {
                    $set('amount', 0);
                    $set('rate', 0);
                    $set('total', 0);
                })
                ->required(),

            ViewField::make('payer_currencies')
                ->label('Currencies')
                ->view('filament.resources.customers.forms.customer-currencies-table')
                ->reactive()
                ->hidden(fn (callable $get) => empty($get('payer_currencies')))
                ->columnSpanFull(),

            Select::make('payer_id')
                ->options(
                    fn ($get) => ($get('type') != CurrencyType::CompanyExpense) ?
                        \App\Models\Customer::select('name', 'id', 'balance')->where('permanent', ExpenseGroup::DEBTORS->value)->get()
                            ->mapWithKeys(fn (\App\Models\Customer $customer) => [
                                $customer->id => sprintf(
                                    '%s (%s SDG)',
                                    $customer->name,
                                    number_format($customer->balance, 2)
                                ),
                            ]) : Company::select('name', 'id', 'type')->get()
                            ->mapWithKeys(fn (\App\Models\Company $company) => [
                                $company->id => sprintf(
                                    '%s ( %s )',
                                    $company->name,
                                    ($company->type?->getLabel())
                                ),
                            ])

                )
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
                    'customer' => fn () => \App\Models\Customer::where('permanent', ExpenseGroup::DEBTORS->value)->get(),
                ])
                ->live()
                ->hidden(fn (callable $get) => in_array($get('type'), [CurrencyType::Convert, CurrencyType::CompanyExpense])),

            Hidden::make('party_type')
                ->hidden(fn (callable $get) => in_array($get('type'), [CurrencyType::Convert, CurrencyType::CompanyExpense])),

            Hidden::make('party_id')
                ->hidden(condition: fn (callable $get) => in_array($get('type'), [CurrencyType::Convert, CurrencyType::CompanyExpense])),

            Select::make('currency_id')
                ->relationship('currency', 'name')
                ->required(),

            Select::make('truck_id')
                ->relationship(name: 'truck', titleAttribute: 'id')
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(condition: fn (callable $get) => in_array($get('type'), [CurrencyType::CompanyExpense])),

            DecimalInput::make('amount')
                ->required()
                ->million()
                ->afterStateUpdated(function ($set, $get) {
                    // استخدام الدالة العالمية هنا أيضاً لضمان الدقة
                    $price = clean_number($get('rate')) ?: 1;
                    $amount = clean_number($get('amount')) ?: 0;

                    $set('total', $amount * $price);
                }),

            DecimalInput::make(name: 'rate')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($set, $get) {
                    // استخدام الدالة العالمية هنا أيضاً لضمان الدقة
                    $price = clean_number($get('rate')) ?: 1;
                    $amount = clean_number($get('amount')) ?: 0;

                    $set('total', $amount * $price);
                })
                ->visible(condition: fn (callable $get) => in_array($get('type'), [CurrencyType::CompanyExpense, CurrencyType::Convert]))
                ->default(1),

            DecimalInput::make('total')
                ->required()
                ->million()
                // ->visible(condition: fn (callable $get) => in_array($get('type'), [CurrencyType::CompanyExpense, CurrencyType::Convert]))
                ->readOnly(),

            TextInput::make('note')
                ->default(null)->columnSpanFull(),
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
