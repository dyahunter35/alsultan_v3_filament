<?php

namespace App\Filament\Resources\Supplyings\Schemas;

use App\Enums\PaymentOptions;
use App\Filament\Forms\Components\DecimalInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplyingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->default(fn() => request()->get('customer_id', null)),
                        Select::make('representative_id')
                            ->relationship('representative', 'name')
                            ->default(null),

                        Select::make('payment_method')
                            ->options(PaymentOptions::class)
                            ->default(null),
                        Hidden::make('paid_amount')
                            ->required(),

                        TextInput::make('statement')
                            ->required(),
                        TextInput::make('payment_reference')
                            ->default(null),
                        DecimalInput::make('total_amount')
                            ->required()
                            ->live(onBlur: true)
                            ->numeric(),
                    ]),
                Section::make()
                    ->columns(1)
                    ->columnSpan(1)
                    ->schema([
                        ToggleButtons::make('is_completed')
                            ->inline()
                            ->boolean()
                            ->grouped()
                            ->default(true)
                            ->required(),

                        DatePicker::make('created_at')

                            ->default(now()),
                    ]),



                Hidden::make('created_by')

                    ->default(fn() => auth()->user()->id),
            ]);
    }
}
