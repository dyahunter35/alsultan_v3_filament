<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class DecimalInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->inputMode('decimal');
        $this->stripCharacters(',');
        $this->rule('numeric');

        // القناع لإظهار الفواصل أثناء الكتابة
        $this->mask(RawJs::make(<<<'JS'
            $money($input, '.', ',', 6)
        JS));
    }

    public function million(bool $condition = true): static
    {
        if ($condition) {
            // 1. عند جلب البيانات من القاعدة: اقسم على مليون
            $this->formatStateUsing(function ($state) {
                return blank($state) ? null : (float) $state / 1000000;
            });
            $this->live(onBlur: true);

            // 2. عند الحفظ في القاعدة: اضرب في مليون
            $this->dehydrateStateUsing(function ($state) {
                // إذا كانت القيمة فارغة أو null، أرجع null
                if (blank($state)) {
                    return null;
                }

                // تنظيف القيمة من الفواصل
                $cleanedState = str_replace(',', '', (string) $state);

                // إذا كانت القيمة المنظفة فارغة أو صفر، أرجع null
                if (blank($cleanedState) || $cleanedState === '0' || $cleanedState === '0.0') {
                    return null;
                }

                $value = (float) $cleanedState;

                // إذا كانت القيمة بعد التحويل صفر، أرجع null
                if ($value == 0) {
                    return null;
                }

                return $value * 1000000;
            });

            // 3. إظهار القيمة الفعلية (القيمة المضروبة) في الـ hint
            $this->hint(function ($state) {
                if (blank($state)) {
                    return null;
                }

                // تنظيف القيمة من الفواصل لإجراء العملية الحسابية
                $cleanValue = (float) str_replace(',', '', $state);
                $actualValue = $cleanValue * 1000000;

                return 'القيمة الفعلية: '.number_format($actualValue);
            });

            $this->hintColor('info');
            $this->hintIcon('heroicon-m-banknotes'); // اختياري لشكل أجمل
        }

        return $this;
    }
}
