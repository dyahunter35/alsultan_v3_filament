<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\CompanyType;
use App\Enums\CurrencyOption;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('company_details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('location')
                            ->required(),
                        Select::make('type')
                            ->options(CompanyType::class)
                            ->required(),
                        Select::make('default_currency')
                            ->nullable()
                            ->options(CurrencyOption::class),
                    ]),

            ]);
    }
    
}
