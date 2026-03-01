<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CargoPriority: string implements HasColor, HasIcon, HasLabel
{
    use EnumsKeys;
    case Weight = 'weight';
    case Qty = 'qty';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Weight => 'وزن',
            self::Qty => 'عدد',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Weight => 'success',
            self::Qty => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Weight => 'heroicon-o-scale',
            self::Qty => 'heroicon-o-hashtag',
        };
    }
}
