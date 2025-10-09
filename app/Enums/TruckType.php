<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use App\Traits\EnumsKeys;
use Filament\Support\Contracts;

enum TruckType: string implements HasLabel, HasColor, HasIcon
{
    use EnumsKeys;
    case Local = 'local';
    case Outer = 'outer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Local => 'محلي',
            self::Outer => 'خارجي',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Local => 'success',
            self::Outer => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Local => 'heroicon-o-arrows-up-down',
            self::Outer => 'heroicon-o-arrow-down',
        };
    }
}
