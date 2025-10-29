<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasLabel;

enum PaymentOptions: string implements HasLabel
{
    use EnumsKeys;

    case BOK = 'bok';
    case IBOK = 'ibok';
    case CHEQUE = 'cheque';
    case CASH = 'cash';
    case FORI = 'fori';
    case MIN_DEP = 'min_dep';
    case PLUS_DEP = 'plus_dep';
    case START_PA = 'start';
    case BANK = 'bank';

    public function getLabel(): string
    {
        return __('enums.payment_option.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::BOK => 'text-success',
            self::CHEQUE => 'text-danger',
            self::CASH => 'text-info',
            self::FORI => 'text-primary',
            self::MIN_DEP => 'text-gray',
            self::PLUS_DEP => 'btn btn-outline-primary',
            self::START_PA => 'btn btn-outline-primary',
            self::BANK => 'btn btn-outline-primary',
        };
    }
}
