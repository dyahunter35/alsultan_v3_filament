<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContractStatus: string implements HasColor, HasLabel, HasIcon
{
    use EnumsKeys;

    case Active = 'active';
    case Completed = 'completed';
    case Terminated = 'terminated';
    case Pending = 'pending';

    public function getLabel(): ?string
    {
        return __('contract.fields.status.options.' . $this->value);
    }
    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'primary',
            self::Completed => 'success',
            self::Terminated => 'danger',
            self::Pending => 'warning',
        };
    }

    // make get icons

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-sparkles',
            self::Pending => 'heroicon-m-arrow-path',
            self::Completed => 'heroicon-m-check',
            self::Terminated => 'heroicon-m-x-circle',
        };
    }
}
