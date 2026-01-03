<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CurrencyOption: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    use EnumsKeys;

    case egy = 'Egy';
    case dolar = 'Dollar';
    case ryal = 'Ryal';
    case aed = 'Aed';
    case inr = 'Inr';
    case qar = 'Qar';

    public function arabic(): string
    {
        return $this->getLabel();
    }

    public function getLabel(): ?string
    {
        return __('enums.currency_option.'.$this->name.'.label');
    }

    public function getDescription(): ?string
    {
        return __('enums.currency_option.'.$this->name.'.description');
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::egy => 'heroicon-o-currency-pound',
            self::dolar => 'heroicon-o-currency-dollar',
            self::ryal => 'heroicon-o-currency-dollar',
            self::aed => 'heroicon-o-currency-dollar',
            self::inr => 'heroicon-o-currency-rupee',
            self::qar => 'heroicon-o-currency-dollar',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::egy => 'warning',
            self::dolar => 'success',
            self::ryal => 'danger',
            self::aed => 'warning',
            self::inr => 'info',
            self::qar => 'primary',
        };
    }
}
