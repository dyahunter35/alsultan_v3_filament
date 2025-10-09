<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Attribute;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpansesType: string implements HasIcon, HasLabel, HasColor
{
    use EnumsKeys;

    case NORMAL = 'normal';
    case CURRENCY = 'currency';
    // store
    #[Group('store')]
    case STORE_TRAN = 'tran';
    #[Group('store')]
    case STORE_FOOD = 'food';
    #[Group('store')]
    case STORE_SAIL = 'salares';
    #[Group('store')]
    case STORE_LOAN = 'loan';
    case STORE_CARRIER = 'carrier';
    case STORE_RENT = 'rent';
    case REP_TRA = 'rep_tra';

    #[Group('government')]
    case CUSTOMS = 'customs'; //جمارك
    case PAPERS = 'paper';
    case TAX = 'tax';
    case GOVERMENT = 'goverment';

    public function arabic(): string
    {
        return match ($this) {
            self::NORMAL => 'مبيعات',
            self::CURRENCY => 'الدائنون',
            self::STORE_TRAN => 'منصرفات ترحيل',
            self::STORE_FOOD => 'منصرفات ميز',
            self::STORE_SAIL => 'مرتبات واجور',
            self::STORE_LOAN => 'سلفيات',
            self::STORE_CARRIER => 'عتالة',
            self::STORE_RENT => 'ايجارات',
            self::REP_TRA => 'تحويل مالي',
            self::CUSTOMS => 'جمارك',
            self::PAPERS => 'شهادات وارد',
            self::TAX => 'ضرائب',
            self::GOVERMENT => 'رسوم حكومية',
        };
    }

    public function getLabel(): ?string
    {
        return $this->arabic();
    }
    public function getColor(): string|array|null
    {
        return 'success';
    }
    public function icon(): string
    {
        return match ($this) {
            self::NORMAL => 'fa fa-user',
            self::CURRENCY => 'fa fa-dollar-sign',
            self::STORE_TRAN => 'fa fa-truck',
            self::STORE_FOOD => 'fal fa-apple-alt',
            self::STORE_SAIL => 'fa fa-dollar-sign',
            self::STORE_LOAN => 'fa fa-dollar-sign',
            self::STORE_CARRIER => 'fa fa-weight',
            self::STORE_RENT => 'fa fa-dollar-sign',
            self::REP_TRA => 'fa fa-dollar-sign',
            self::CUSTOMS => 'fal fa-cars',
            self::PAPERS => 'fa fa-certificate',
            self::TAX => 'fa fa-dollar-sign',
            self::GOVERMENT => 'fa fa-cogs',
        };
    }


    public function getIcon(): ?string
    {
        return 'heroicon-o-user';
        return match ($this) {


            /* self::NORMAL => 'heroicon-o-user',
            self::CURRENCY => 'heroicon-o-currency-dollar',
            self::STORE_TRAN => 'heroicon-o-truck',
            self::STORE_FOOD => 'heroicon-o-cake',
            self::STORE_SAIL => 'heroicon-o-credit-card',
            self::STORE_LOAN => 'heroicon-o-cash',
            self::STORE_CARRIER => 'heroicon-o-archive',
            self::STORE_RENT => 'heroicon-o-home',
            self::REP_TRA => 'heroicon-o-refresh',
            self::CUSTOMS => 'heroicon-o-globe-alt',
            self::PAPERS => 'heroicon-o-document-text',
            self::TAX => 'heroicon-o-receipt-tax',
            self::GOVERMENT => 'heroicon-o-cog', */
        };
    }


    public function color(): string
    {
        return match ($this) {
            self::NORMAL => 'btn-succes',
            self::CURRENCY => 'btn-outline-info',
            self::STORE_TRAN => 'btn-outline-info',
            self::STORE_FOOD => 'btn-outline-info',
            self::STORE_SAIL => 'btn-outline-info',
            self::STORE_LOAN => 'btn-outline-info',
            self::STORE_RENT => 'btn-outline-info',
            self::STORE_CARRIER => 'btn-outline-info',
            self::REP_TRA => 'btn-outline-success',
            self::CUSTOMS => 'btn-outline-info',
            self::PAPERS => 'btn-outline-info',
            self::TAX => 'btn-outline-info',
            self::GOVERMENT => 'btn-outline-info',
        };
    }


}
