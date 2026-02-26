<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TruckState: string implements HasColor, HasIcon, HasLabel, HasDescription
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

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::OnWay => 'في الطريق',
            self::reach => 'وصلت الي المخزن ',
            self::barn => 'في الحظيرة',
            self::port => 'في الميناء',
        };
    }
}
