<?php

namespace App\Filament\Resources\Ports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PortForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description')
                    ->default(null),
            ]);
    }
}
