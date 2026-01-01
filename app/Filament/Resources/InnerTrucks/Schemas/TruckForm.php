<?php

namespace App\Filament\Resources\InnerTrucks\Schemas;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use App\Models\Branch;
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

                    //self::financialSection(),
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
                    ->required(),

                Forms\Components\DatePicker::make('arrive_date')
                    ->label(__('truck.fields.arrive_date.label'))
                    ->live(onBlur: true),

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

                Forms\Components\Hidden::make('type')
                    ->default(TruckType::Local->value),

                Forms\Components\Hidden::make('from_type')
                    ->default(Branch::class),

                Forms\Components\Select::make('from_id')
                    ->label(__('truck.fields.from_branch.label'))
                    ->options(Branch::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('branch_to')
                    ->label(__('truck.fields.to.label'))
                    ->options(Branch::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ]);
    }

    /**
     * Section for trip and financial details.
     */
    protected static function financialSection(): Schemas\Components\Section
    {
        return Schemas\Components\Section::make(__('truck.sections.financial_info'))
            ->schema([]);
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
                    ->options([
                        TruckState::OnWay->value => TruckState::OnWay->getLabel(),
                        TruckState::reach->value => TruckState::reach->getLabel(),
                    ])
                    ->colors([
                        TruckState::OnWay->value => TruckState::OnWay->getColor(),
                        TruckState::reach->value => TruckState::reach->getColor(),
                    ])
                    ->icons([
                        TruckState::OnWay->value => TruckState::OnWay->getIcon(),
                        TruckState::reach->value => TruckState::reach->getIcon(),
                    ])
                    ->required(),

                Forms\Components\Textarea::make('note')
                    ->label(__('truck.fields.note.label'))
                    ->maxLength(190),
            ]);
    }
}
