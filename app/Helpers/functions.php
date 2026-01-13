<?php

use Carbon\Carbon;

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

if (! function_exists('parseDateRange')) {

    function parseDateRange($range): array
    {
        /* if (! $range) {
            return [now()->startOfDay(), now()->endOfDay()];
        } */

        $dates = explode(' - ', $range);
        /*  if (count($dates) !== 2) {
            return [now()->startOfDay(), now()->endOfDay()];
        } */

        try {
            $from = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
            return [$from, $to];
        } catch (\Exception $e) {
            return [null, null];
        }
    }
}

if (! function_exists('another_expense')) {
    function another_expense(array $data, array $filter = ['payment_reference']): array
    {
        return array_keys(\Illuminate\Support\Arr::except($data, $filter));
    }
}
