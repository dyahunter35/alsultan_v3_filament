<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CurrencyType: string implements HasColor, HasIcon, HasLabel
{
    use EnumsKeys;

    case SEND = 'send';
    case Convert = 'convert';
    case CompanyExpense = 'company';

    public function getLabel(): ?string
    {
        return __('enums.currency_type.'.$this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SEND => 'heroicon-o-arrow-right-start-on-rectangle',
            self::Convert => 'heroicon-o-arrow-path-rounded-square',
            self::CompanyExpense => 'heroicon-o-building-office-2',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SEND => 'success',
            self::Convert => 'info',
            self::CompanyExpense => 'danger',
        };
    }
}
