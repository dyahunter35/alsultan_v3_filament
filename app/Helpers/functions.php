<?php

if (! function_exists('clean_number')) {
    /**
     * ينظف النصوص ويحولها إلى رقم عشري نقي
     */
    function clean_number($value): ?float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        // إذا كان الرقم أصلاً float أو integer لا داعي للتنظيف
        if (is_numeric($value) && ! is_string($value)) {
            return (float) $value;
        }

        // تنظيف النص: إزالة كل شيء ما عدا الأرقام والنقطة العشرية
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return is_numeric($clean) ? (float) $clean : 0.0;

    }
}
