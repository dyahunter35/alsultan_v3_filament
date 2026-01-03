<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class Setting extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static bool $shouldRegisterNavigation = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }
}
