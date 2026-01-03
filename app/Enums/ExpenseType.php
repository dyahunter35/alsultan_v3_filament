<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpenseType: string implements HasIcon, HasLabel
{
    use EnumsKeys;

    case SALE = 'sale';                   // مبيعات

    #[Group('debtors')]
    case DEBTORS = 'debtors';             // الدائنون

    // Currency expenses
    #[Group('currency')]
    case SALARIES = 'salaries';           // مرتبات وأجور
    #[Group('currency')]
    case ADVANCES = 'advances';           // سلفيات
    #[Group('currency')]
    case REPRESENTATIVE_TRANSFER = 'rep_transfer'; // التحويل المالي للمندوب

    // Store expenses
    #[Group('store')]
    case TRANSPORT = 'transport';         // مصروفات ترحيل

    #[Group('store')]
    case FOOD = 'food';                     // مصروفات ميز

    #[Group('store')]
    case CARRIER = 'carrier';             // عتالة
    #[Group('store')]
    case RENT = 'rent';                   // إيجارات

    // Representative transfer

    // Government
    #[Group('government')]
    case CUSTOMS = 'customs';             // جمارك
    #[Group('government')]
    case CERTIFICATES = 'certificates';   // شهادات وارد
    #[Group('government')]
    case TAX = 'tax';                      // ضرائب
    #[Group('government')]
    case GOVERNMENT_FEES = 'government_fees'; // رسوم حكومية

    public function arabic(): string
    {
        return match ($this) {
            self::SALE => 'مبيعات',
            self::DEBTORS => 'الدائنون',
            self::TRANSPORT => 'منصرفات ترحيل',
            self::FOOD => 'منصرفات ميز',
            self::SALARIES => 'مرتبات وأجور',
            self::ADVANCES => 'سلفيات',
            self::CARRIER => 'عتالة',
            self::RENT => 'إيجارات',
            self::REPRESENTATIVE_TRANSFER => 'تحويل مالي للمندوب',
            self::CUSTOMS => 'جمارك',
            self::CERTIFICATES => 'شهادات وارد',
            self::TAX => 'ضرائب',
            self::GOVERNMENT_FEES => 'رسوم حكومية',
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

    public function getIcon(): ?string
    {
        return 'heroicon-m-user'; /* match ($this) {
            self::SALE => 'fa fa-user',
            self::DEBTORS => 'fa fa-dollar-sign',
            self::TRANSPORT => 'fa fa-truck',
            self::FOOD => 'fal fa-apple-alt',
            self::SALARIES => 'fa fa-dollar-sign',
            self::ADVANCES => 'fa fa-dollar-sign',
            self::CARRIER => 'fa fa-weight',
            self::RENT => 'fa fa-dollar-sign',
            self::REPRESENTATIVE_TRANSFER => 'fa fa-dollar-sign',
            self::CUSTOMS => 'fal fa-cars',
            self::CERTIFICATES => 'fa fa-certificate',
            self::TAX => 'fa fa-dollar-sign',
            self::GOVERNMENT_FEES => 'fa fa-cogs',
        }; */
    }

    public function color(): string
    {
        return match ($this) {
            self::SALE => 'btn-success',
            self::DEBTORS => 'btn-outline-info',
            self::TRANSPORT => 'btn-outline-info',
            self::FOOD => 'btn-outline-info',
            self::SALARIES => 'btn-outline-info',
            self::ADVANCES => 'btn-outline-info',
            self::RENT => 'btn-outline-info',
            self::CARRIER => 'btn-outline-info',
            self::REPRESENTATIVE_TRANSFER => 'btn-outline-success',
            self::CUSTOMS => 'btn-outline-info',
            self::CERTIFICATES => 'btn-outline-info',
            self::TAX => 'btn-outline-info',
            self::GOVERNMENT_FEES => 'btn-outline-info',
        };
    }

    public static function customerSelectOptions(): array
    {
        return [
            self::SALE->value => self::SALE->getLabel(),
            self::GOVERNMENT_FEES->value => self::GOVERNMENT_FEES->getLabel(),
            self::DEBTORS->value => self::DEBTORS->getLabel(),
            self::CUSTOMS->value => self::CUSTOMS->getLabel(),
            self::TAX->value => self::TAX->getLabel(),
        ];
    }
}
