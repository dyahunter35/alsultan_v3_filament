<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('company_details')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('name'),
                        TextEntry::make('location'),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('default_currency')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
