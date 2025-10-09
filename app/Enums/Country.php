<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum Country: string implements HasLabel, HasColor
{
    use EnumsKeys;
    case Egypt = 'egypt';
    case Qatar = 'qatar';
    case Hind = 'hind';
    case Sudan = 'sudan';

    public function getLabel(): string
    {
        return __('enums.country.'.$this->value);
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Egypt => 'primary',
            self::Qatar => 'secondary',
            self::Hind => 'gray',
            self::Sudan => 'info',
        };
    }
}
