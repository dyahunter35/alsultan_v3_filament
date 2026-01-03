<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CompanyType: string implements HasColor, HasIcon, HasLabel
{
    use EnumsKeys;

    case Company = 'company';
    case Contractor = 'contractor';

    public function getLabel(): ?string
    {
        return __('enums.company_type.'.$this->value);
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Company => 'success',
            self::Contractor => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Company => 'heroicon-o-arrows-up-down',
            self::Contractor => 'heroicon-o-arrow-down',
        };
    }
}
