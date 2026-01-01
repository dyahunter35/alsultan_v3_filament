<?php

namespace App\Filament\Resources\Trucks\Schemas;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use App\Filament\Forms\Components\DecimalInput;
use App\Models\Port;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class TruckForm
{
    /**
     * Configure the Truck form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Grid::make(1)
                ->columnSpan(2)
                ->schema([
                    self::driverSection(),
                    self::contractSection(),
                ]),

            Schemas\Components\Grid::make(1)
                ->schema([

                    self::financialSection(),
                    self::statusSection(),
                ])->columnSpan(1)
        ])->columns(3);
    }

    /**
     * Section for driver and truck information.
     */
    protected static function driverSection(): Schemas\Components\Section
    {
        return Schemas\Components\Section::make(__('truck.sections.driver_info'))
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

                Forms\Components\Select::make('category_id')
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
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($get, $set) => self::calculateForm($get, $set)),

                Forms\Components\DatePicker::make('arrive_date')
                    ->label(__('truck.fields.arrive_date.label'))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($get, $set) => self::calculateForm($get, $set)),

                Forms\Components\TextInput::make('truck_model')
                    ->label(__('truck.fields.truck_model.label'))
                    ->required()
                    ->maxLength(190),
            ])->columns(2);
    }

    /**
     * Section for contractor and route info.
     */
    protected static function contractSection(): Schemas\Components\Section
    {
        return Schemas\Components\Section::make(__('truck.sections.contract_info'))
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('contractor_id')
                    ->label(__('truck.fields.contractor_id.label'))
                    ->relationship('contractorInfo', 'name')
                    ->searchable()
                    ->default(fn() => request()->get('contractor_id')) // <-- pre-fill
                    ->preload(),

                Forms\Components\Select::make('company_id')
                    ->label(__('truck.fields.company_id.label'))
                    ->relationship('companyId', 'name')
                    ->searchable()
                    ->default(fn() => request()->get('company_id')) // <-- pre-fill
                    ->preload(),

                Forms\Components\Hidden::make('type')
                    ->default(TruckType::Outer->value),

                Forms\Components\Hidden::make('from_type')
                    ->default(Port::class),

                Forms\Components\Select::make('from_id')
                    ->label(__('truck.fields.from.label'))
                    ->options(Port::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('branch_to')
                    ->label(__('truck.fields.to.label'))
                    ->relationship('toBranch', 'name')
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
            ]);
    }

    /**
     * Section for trip and financial details.
     */
    protected static function financialSection(): Schemas\Components\Section
    {
        return Schemas\Components\Section::make(__('truck.sections.financial_info'))
            ->schema([
                Schemas\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('agreed_duration')
                        ->label(__('truck.fields.agreed_duration.label'))
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($get, $set) => self::calculateForm($get, $set)),

                    Forms\Components\TextInput::make('delay_day_value')
                        ->label(__('truck.fields.delay_day_value.label'))
                        ->numeric()
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($get, $set) => self::calculateForm($get, $set))
                        ->suffix('SDG')
                        ->hint(fn($state) => number_format($state ?? 0)),

                    Forms\Components\TextInput::make('trip_days')
                        ->label(__('truck.fields.trip_days.label'))
                        ->numeric()
                        ->readOnly()
                        ->visible(fn($get) => self::hasValidDates($get)),

                    Forms\Components\TextInput::make('diff_trip')
                        ->label(__('truck.fields.diff_trip.label'))
                        ->numeric()
                        ->readOnly()
                        ->visible(fn($get) => ($get('diff_trip') ?? 0) > 0),
                ]),

                Schemas\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('delay_value')
                        ->label(__('truck.fields.delay_value.label'))
                        ->numeric()
                        ->visible(fn($get) => ($get('diff_trip') ?? 0) > 0)
                        ->hint(fn($state) => number_format($state ?? 0)),

                    DecimalInput::make('truck_fare')
                        ->label(__('truck.fields.truck_fare.label'))
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($get, $set) => self::calculateTotal($get, $set)),
                ]),

                Forms\Components\TextInput::make('total_amount')
                    ->label(__('truck.fields.total_amount.label'))
                    ->numeric()
                    ->readOnly()
                    ->suffix('SDG')
                    ->helperText(fn($state) => number_format($state ?? 0)),
            ]);
    }

    /**
     * Section for truck status and notes.
     */
    protected static function statusSection(): Schemas\Components\Section
    {
        return Schemas\Components\Section::make(__('truck.sections.status_info'))
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
            ]);
    }

    /**
     * Utility: check if both dates are valid.
     */
    protected static function hasValidDates(Get $get): bool
    {
        return !is_null($get('pack_date')) && !is_null($get('arrive_date'));
    }

    /**
     * Core logic: calculate trip days, delay, and total.
     */
    public static function calculateForm(Get $get, Set $set): void
    {
        if (!self::hasValidDates($get)) return;

        $from = Carbon::parse($get('pack_date'));
        $to = Carbon::parse($get('arrive_date'));

        if ($to->lessThan($from)) {
            $set('arrive_date', $get('pack_date'));
            return;
        }

        $trip = $from->diffInDays($to) + 1;
        $set('trip_days', $trip);

        $agreed = (int) $get('agreed_duration');
        $diff = max($trip - $agreed, 0);
        $set('diff_trip', $diff);

        $delayValue = self::calculateDelayValue($diff, (int) $get('delay_day_value'));
        $set('delay_value', $delayValue);

        self::calculateTotal($get, $set);
    }

    /**
     * Helper: calculate total amount.
     */
    public static function calculateTotal(Get $get, Set $set): void
    {
        $total = (int) $get('delay_value') + (int) $get('truck_fare');
        $set('total_amount', $total);
    }

    /**
     * Helper: calculate delay cost.
     */
    protected static function calculateDelayValue(int $days, int $valuePerDay): int
    {
        return $days > 0 ? $days * $valuePerDay : 0;
    }
}
