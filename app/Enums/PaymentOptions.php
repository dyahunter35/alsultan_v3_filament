<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentOptions: string implements HasLabel
{
    case BOK = 'bok';
    case IBOK = 'ibok';
    case CHEQUE = 'cheque';
    case CASH = 'cash';
    case FORI = 'fori';
    case MIN_DEP = 'min_dep';
    case PLUS_DEP = 'plus_dep';
    case START_PA = 'start';
    case BANK = 'bank';

    public function getLabel(): ?string
    {
        return (app()->getLocale() == 'en') ? match ($this) {
            self::BOK => 'BOK',
            self::IBOK => 'IBOK',
            self::CHEQUE => 'Cheque',
            self::CASH => 'Cash',
            self::FORI => 'Fori',
            self::MIN_DEP => 'Min Dep',
            self::PLUS_DEP => 'Plus Dep',
            self::START_PA => 'Start Pa',
            self::BANK => 'Bank Transfer',
        } :
            match ($this) {
                self::BOK => 'بنكك',
                self::IBOK => 'اي بوك',
                self::CHEQUE => 'شيك',
                self::CASH => 'كاش',
                self::FORI => 'فوري',
                self::MIN_DEP => 'خصم قيمة ترحيل',
                self::PLUS_DEP => 'إضافة قيمة ترحيل',
                self::START_PA => 'رصيد سابق مرحل',
                self::BANK => 'تحويل بنكي',
            };
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
