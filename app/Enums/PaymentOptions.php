<?php

namespace App\Enums;

enum PaymentOptions: string
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

    public function arabic(): string
    {
        return match ($this) {
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
