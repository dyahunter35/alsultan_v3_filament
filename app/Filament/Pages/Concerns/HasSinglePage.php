<?php

namespace App\Filament\Pages\Concerns;

use BackedEnum;
use Filament\Resources\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Contracts\Support\Htmlable;

trait HasSinglePage
{
    use HasTranslateConfigure;

    public static function getLocalePath(): string
    {

        return Str::of(class_basename(static::class))->snake();
    }

    public static function getLocale($key): string | null
    {
        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . $key, [], app()->getLocale())) {
            return trans($locale);
        }

        return null;
    }

    public function getHeading(): string | Htmlable
    {
        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . 'navigation.heading', [], app()->getLocale())) {
            return trans($locale);
        }
        return $this->heading ?? $this->getTitle();
    }

    public function getSubheading(): string | Htmlable | null
    {

        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . 'navigation.sub_heading.', [], app()->getLocale())) {
            return trans($locale);
        }
        return null;
    }

    public static function getActiveNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . 'navigation.icon', [], app()->getLocale())) {
            return trans($locale);
        }
        return static::$activeNavigationIcon ?? static::getNavigationIcon();
    }

    public static function getNavigationLabel(): string
    {
        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . 'navigation.heading', [], app()->getLocale())) {
            return trans($locale);
        }
        return static::$navigationLabel ?? static::$title ?? str(class_basename(static::class))
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }
    public function getTitle(): string | Htmlable
    {
        return static::$title ?? (string) str(class_basename(static::class))
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }
}
