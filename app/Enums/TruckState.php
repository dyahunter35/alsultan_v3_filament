<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum TruckState: string implements HasLabel, HasColor, HasIcon
{
    use EnumsKeys;
    case OnWay = 'on_way';
    case reach = 'reach';
    case barn = 'barn';
    case port = 'port';

    public function arabic(): string
    {
        return __('enums.truck_state.' . $this->value);
    }

    public function getLabel(): ?string
    {
        return $this->arabic();
    }
    public function getColor(): ?string
    {
        return match ($this) {
            self::OnWay => 'warning',
            self::reach => 'success',
            self::barn => 'primary',
            self::port => 'info',
        };
    }
    public function getIcon(): ?string
    {
        return match ($this) {
            self::OnWay => 'heroicon-o-arrow-right',
            self::reach => 'heroicon-o-check-circle',
            self::barn => 'heroicon-o-home',
            self::port => 'heroicon-o-home',
        };
    }
}
