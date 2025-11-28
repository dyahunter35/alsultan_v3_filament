<?php

namespace App\Filament\Pages\Concerns;

use BackedEnum;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

trait HasReport
{

    public function getReportParameters(): array
    {
        return [];
    }


    /**
     * إرجاع المفتاح الفريد للتقرير من اسم الكلاس
     */
    public static function getReportKey(): string
    {
        return Str::of(class_basename(static::class))->snake();
    }

    /**
     * إرجاع بيانات التقرير من ملف الترجمة
     */
    public static function getReportData(): array
    {
        $key = static::getReportKey();

        $reports = trans('report');

        if (is_array($reports) && isset($reports[$key])) {
            return $reports[$key];
        }

        return [];
    }

    public static function getNavigationSort(): ?int
    {
        $data = static::getReportData();
        return $data['sort'] ?? null;
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('report.group') ?? null;
    }

    /**
     * العنوان الرئيسي للتقرير
     */
    public function getHeading(): string | Htmlable
    {
        $data = static::getReportData();
        $params = $this->getReportParameters();

        if (isset($data['heading'])) {
            return __($data['heading'], $params);
        }

        return __($this->getTitle(), $params);
    }


    /**
     * الأيقونة الخاصة بالتقرير
     */
    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        $data = static::getReportData();
        return $data['icon'] ?? null;
    }


    /**
     * العنوان المستخدم في قائمة التنقل
     */
    public static function getNavigationLabel(): string
    {
        $data = static::getReportData();
        //dd($data);
        return $data['heading'] ?? str(class_basename(static::class))
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }

    /**
     * العنوان الافتراضي للصفحة
     */
    public function getTitle(): string | Htmlable
    {
        $data = static::getReportData();
        $params = $this->getReportParameters();

        $title = $data['heading']
            ?? str(class_basename(static::class))
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();

        return __($title, $params);
    }
}
