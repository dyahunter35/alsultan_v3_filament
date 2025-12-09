<?php

namespace App\Filament\Pages\Concerns;

use BackedEnum;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

trait HasReport
{
    // --- الدوال الأساسية للبيانات والبارامترات ---

    /**
     * إرجاع بارامترات التقرير لاستخدامها في الترجمة (مثل :p و :b)
     */
    public function getReportParameters(): array
    {
        return [];
    }

    /**
     * إرجاع المفتاح الفريد للتقرير من اسم الكلاس (مثل: single_stock_report)
     */
    public static function getReportKey(): string
    {
        return Str::of(class_basename(static::class))->snake()->toString();
    }

    /**
     * إرجاع بيانات التقرير من ملف الترجمة
     * (تحسين: الوصول المباشر عبر المسار الكامل report.key)
     * @return array<string, mixed>
     */
    public static function getReportData(): array
    {
        $key = static::getReportKey();
        $fullPath = 'report.' . $key;

        $reportData = trans($fullPath);

        // إذا لم يتم العثور على ترجمة، فإن trans() تعيد المسار نفسه (fullPath).
        // نتحقق إذا كانت القيمة المعادة مصفوفة، وإذا كانت مختلفة عن المسار، نرجعها.
        if (is_array($reportData) && $reportData !== $fullPath) {
            return $reportData;
        }

        return [];
    }

    // --- الدوال الخاصة بالتنقل والعناوين ---

    public static function getNavigationSort(): ?int
    {
        return data_get(static::getReportData(), 'sort');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        $group = __('report.group');
        return is_string($group) && $group !== 'report.group' ? $group : null;
    }

    /**
     * العنوان الرئيسي للتقرير
     * (التحسين هنا: تمرير البارامترات $params إلى دالة __() بعد جلب مفتاح الترجمة)
     */
    public function getHeading(): string | Htmlable
    {
        $data = static::getReportData();
        // 1. جلب البارامترات من دالة getReportParameters()
        $params = $this->getReportParameters();

        // 2. محاولة جلب مفتاح الترجمة (يفترض أنه مفتاح وليس نصاً صريحاً)
        if ($headingKey = data_get($data, 'heading')) {
            // 3. استخدام دالة __() لترجمة المفتاح وتبديل المتغيرات بالقيم من $params
            return __($headingKey, $params);
        }

        // في حال عدم وجود 'heading' في بيانات التقرير
        return $this->getTitle();
    }


    /**
     * الأيقونة الخاصة بالتقرير
     */
    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return data_get(static::getReportData(), 'icon');
    }


    /**
     * العنوان المستخدم في قائمة التنقل
     */
    public static function getNavigationLabel(): string
    {
        $data = static::getReportData();

        if ($labelKey = data_get($data, 'heading')) {
            // ملاحظة: لا نمرر $params هنا، لأن عناوين التنقل (Navigation Labels)
            // عادةً لا تحتاج إلى بارامترات ديناميكية (مثل أسماء المنتجات).
            // نستخدم الترجمة البسيطة فقط.
            $translatedLabel = __($labelKey);
            return (is_string($translatedLabel) && $translatedLabel !== $labelKey) ? $translatedLabel : $labelKey;
        }

        return (string) str(class_basename(static::class))
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
        // جلب البارامترات لتضمينها في العنوان
        $params = $this->getReportParameters() ?? [];

        $titleKey = data_get($data, 'heading');

        if ($titleKey) {
            // ترجمة العنوان مع تمرير البارامترات
            $title = __($titleKey, $params);
        } else {
            // إنشاء العنوان الافتراضي من اسم الكلاس
            $title = (string) str(class_basename(static::class))
                ->kebab()
                ->replace('-', ' ')
                ->ucwords();
        }

        return $title;
    }
}
