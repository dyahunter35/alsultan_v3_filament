<?php

namespace App\Filament\Resources\Trucks\Schemas;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use App\Models\Port;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas;

class TruckForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Schemas\Components\Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Schemas\Components\Grid::make(1)
                            ->schema([
                                Schemas\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('driver_name')
                                            ->label(__('truck.fields.driver_name.label'))
                                            ->required()
                                            ->maxLength(190),

                                        Forms\Components\TextInput::make('driver_phone')
                                            ->label(__('truck.fields.driver_phone.label'))
                                            ->tel()
                                            ->prefix("+")
                                            ->placeholder("999999999")
                                            ->required()
                                            ->maxLength(190),

                                        Forms\Components\Select::make('products_cateogry_id')
                                            ->label(__('truck.fields.category.label'))
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\TextInput::make('car_number')
                                            ->label(__('truck.fields.car_number.label'))
                                            ->required()
                                            ->maxLength(190),

                                        Forms\Components\DatePicker::make('pack_date')
                                            ->label(__('truck.fields.pack_date.label'))
                                            ->afterStateUpdated(function ($get, $set) {
                                                self::calculateForm($get, $set);
                                            })
                                            ->required()
                                            ->live(onBlur: true),

                                        Forms\Components\DatePicker::make('arrive_date')
                                            ->label(__('truck.fields.arrive_date.label'))
                                            ->afterStateUpdated(function ($get, $set) {
                                                self::calculateForm($get, $set);
                                            })
                                            ->live(onBlur: true),

                                        Forms\Components\TextInput::make('truck_model')
                                            ->label(__('truck.fields.truck_model.label'))
                                            ->required()
                                            ->maxLength(190),
                                    ])->columns(2),



                                Schemas\Components\Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('contractor_id')
                                            ->label(__('truck.fields.contractor_id.label'))
                                            ->relationship('contractorInfo', 'name')
                                            ->label(__('truck.fields.contractor_id.label'))

                                            ->preload()
                                            ->searchable(),

                                        Forms\Components\Select::make('company_id')
                                            ->label(__('truck.fields.company_id.label'))
                                            ->relationship('companyId', 'name')
                                            ->preload()
                                            ->searchable(),

                                        Forms\Components\Hidden::make('type')
                                            ->label(__('truck.fields.type.label'))
                                            ->default(TruckType::Outer->value)
                                            ->required(),

                                        Forms\Components\Hidden::make('from_type')
                                            ->label(__('truck.fields.from_type.label'))
                                            ->default(Port::class)
                                            ->required(),

                                        Forms\Components\Select::make('from_id')
                                            ->label(__('truck.fields.from.label'))
                                            ->options(\App\Models\Port::pluck('name', 'id'))
                                            ->required()
                                            ->preload()
                                            ->searchable(),

                                        Forms\Components\Select::make('to')
                                            ->label(__('truck.fields.to.label'))
                                            ->options(fn() => \App\Models\Branch::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('country')
                                            ->label(__('truck.fields.country.label'))
                                            ->options(Country::class)
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\TextInput::make('city')
                                            ->label(__('truck.fields.city.label'))
                                            ->maxLength(190),
                                    ])
                            ])
                    ])
                    ->columnSpan(2),

                Schemas\Components\Section::make()
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('agreed_duration')
                                    ->label(__('truck.fields.agreed_duration.label'))
                                    ->integer()
                                    ->minValue(0)
                                    ->afterStateUpdated(function ($get, $set) {
                                        self::calculateForm($get, $set);
                                    })
                                    ->live(onBlur: true)
                                    ->required(),

                                Forms\Components\TextInput::make('delay_day_value')
                                    ->label(__('truck.fields.delay_day_value.label'))
                                    ->integer()
                                    ->required()
                                    ->afterStateUpdated(function ($get, $set) {
                                        self::calculateForm($get, $set);
                                    })
                                    ->live(onBlur: true)
                                    ->hint(fn($state) => number_format($state)),

                                Forms\Components\TextInput::make('trip_days')
                                    ->label(__('truck.fields.trip_days.label'))
                                    ->integer()
                                    ->required()
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->visible(fn($get) => (!is_null($get('pack_date')) && !is_null($get('arrive_date')))),

                                Forms\Components\TextInput::make('diff_trip')
                                    ->label(__('truck.fields.diff_trip.label'))
                                    ->integer()
                                    ->required()
                                    ->dehydrated()
                                    ->readOnly()
                                    ->visible(fn($get) => $get('diff_trip') > 0),
                            ]),


                        Schemas\Components\Grid::make(2)
                            ->schema([

                                Forms\Components\TextInput::make('delay_value')
                                    ->label(__('truck.fields.delay_value.label'))
                                    ->integer()
                                    ->visible(fn($get) => $get('diff_trip') > 0)
                                    ->required()
                                    ->helperText(fn($state) => number_format($state)),

                                Forms\Components\TextInput::make('truck_fare')
                                    ->label(__('truck.fields.truck_fare.label'))
                                    ->integer()
                                    ->afterStateUpdated(function ($get, $set) {
                                        self::calculateTotal($get, $set);
                                    })
                                    ->live(onBlur: true)
                                    ->helperText(fn($state) => number_format($state))
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('total_amount')
                            ->label(__('truck.fields.total_amount.label'))
                            ->readOnly()
                            ->helperText(fn($state) => number_format($state)),
                        /* Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('truck_fare')
                                            ->label('Created at')
                                            ->content(fn( $get): ?string => $get('truck_fare')),

                                        Forms\Components\Placeholder::make('total')
                                            ->label(__('truck.fields.total_amount.label'))
                                            ->content(fn( $get): ?string =>number_format( $get('total_amount'))),
                                    ])
                                    ->columnSpan(['lg' => 1])
                                    ->hidden(fn($get) => $get('total_amount') === null), */

                        Schemas\Components\Section::make('')
                            ->schema([

                                Forms\Components\ToggleButtons::make('truck_status')
                                    ->label(__('truck.fields.truck_status.label'))
                                    ->inline()
                                    ->default(TruckState::OnWay)
                                    ->options(TruckState::class)
                                    ->required(),

                                Forms\Components\Textarea::make('note')
                                    ->label(__('truck.fields.note.label'))
                                    ->maxLength(190),
                            ]),
                    ])

            ])->columns(3);
    }

    public static function calculateForm(Get $get, Set $set)
    {
        if (is_null($get('pack_date')) || is_null($get('arrive_date'))) {
            return;
        }
        $from = Carbon::parse($get('pack_date'));
        $to = Carbon::parse($get('arrive_date'));

        if ($to < $from) {

            $set('arrive_date', $get('pack_date'));
            return;
        }

        $trip = $from->diffInDays($to) + 1;
        $set('trip_days', $trip);

        $agreed = intval($get('agreed_duration'));

        if (is_null($trip) || is_null($agreed) || !is_integer($trip) || !is_integer($agreed))
            return;

        $diff = $trip - $agreed;
        $set('diff_trip', $diff > 0 ? $diff : 0);

        $delay_day_value = $get('delay_day_value');

        if (is_null($delay_day_value))
            return;

        $set('delay_value', $diff > 0 ? $diff * $delay_day_value : 0);

        self::calculateTotal($get, $set);
    }

    public static function calculateTotal(Get $get, Set $set)
    {
        $delay_value = $get('delay_value') ?? 0;
        $truck_fare = $get('truck_fare');

        if (is_null($delay_value) || is_null($truck_fare))
            return;

        $set('total_amount', ($delay_value + $truck_fare));
    }
}
