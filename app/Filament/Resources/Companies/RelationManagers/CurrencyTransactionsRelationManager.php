<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\CurrencyTransactions\CurrencyTransactionResource;
use App\Models\Currency;
use App\Models\CurrencyBalance;
use App\Models\CurrencyTransaction;
use App\Models\Customer;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Table;

class CurrencyTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'currencyTransactions';

    protected static ?string $relatedResource = CurrencyTransactionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                    CreateAction::make()
                        ->schema([
                                Grid::make(2)->schema([
                                    Hidden::make('type')
                                        // ->options(CurrencyType::class)
                                        ->default(CurrencyType::CompanyExpense)
                                        ->required(),

                                    Hidden::make('payer_type')
                                        ->default(Customer::class)
                                        ->required(),

                                    ViewField::make('payer_currencies')
                                        ->label('Currencies')
                                        ->view('filament.resources.customers.forms.customer-currencies-table')
                                        ->reactive()
                                        ->hidden(fn(callable $get) => empty($get('payer_currencies')))
                                        ->columnSpanFull(),

                                    Select::make('payer_id')
                                        ->label(__('currency_transaction.fields.payer.label'))
                                        ->options(
                                            Customer::select('name', 'id', 'balance')->where('permanent', ExpenseGroup::CURRENCY->value)->get()
                                                ->mapWithKeys(fn(Customer $customer) => [
                                                    $customer->id => sprintf(
                                                        '%s (%s SDG)',
                                                        $customer->name,
                                                        number_format($customer->balance, 2)
                                                    ),
                                                ])
                                        )
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            $currencies = CurrencyBalance::where('owner_type', Customer::class)
                                                ->where('owner_id', $state)
                                                ->get();

                                            $set('payer_currencies', $currencies);
                                        }),

                                    Select::make('currency_id')
                                        ->label(__('currency_transaction.fields.currency.label'))
                                        ->options(Currency::all()->pluck('name', 'id'))
                                        ->default($this->ownerRecord->currency_id)
                                        ->required(),

                                    TextInput::make('amount')
                                        ->label(__('currency_transaction.fields.amount.label'))
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('total', $state * 1);
                                        })
                                        ->required(),
                                    Hidden::make('rate')
                                        ->default(1)
                                        ->required(),
                                    TextInput::make('note')
                                        ->label(__('currency_transaction.fields.note.label')),
                                    Hidden::make('truck_id'),
                                    Hidden::make('total'),
                                ]),
                            ])
                ]);
    }
}
