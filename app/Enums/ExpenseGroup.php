<?php

namespace App\Enums;

use App\Traits\EnumsKeys;
use Attribute;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpenseGroup: string implements HasIcon, HasLabel
{
    use EnumsKeys;

    case SALE = 'sale';                   // مبيعات

    case DEBTORS = 'debtors';             // الدائنون

        // Currency expenses
    case CURRENCY = 'currency';           // مرتبات وأجور

        // Store expenses
    case STORE = 'store';         // مصروفات ترحيل

        // Government
    case CUSTOMS = 'customs';             // جمارك

    case CERTIFICATES = 'certificates';   // شهادات وارد

    case TAX = 'tax';                      // ضرائب

    case GOVERNMENT_FEES = 'government_fees'; // رسوم حكومية


    public function getLabel(): ?string
    {
        return __('enums.expense_group.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return 'success';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-m-user';/* match ($this) {
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
