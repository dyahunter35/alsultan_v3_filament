<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CurrencyType: string implements HasLabel, HasIcon, HasColor
{
    use EnumsKeys;

    case SEND = 'send';
    case Convert = 'convert';

    public function getLabel(): ?string
    {
        return __('enums.currency_type.' . $this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SEND => 'heroicon-o-arrow-right-start-on-rectangle',
            self::Convert => 'heroicon-o-arrow-path-rounded-square',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SEND => 'success',
            self::Convert => 'info',
        };
    }
}
