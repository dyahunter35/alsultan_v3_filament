<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Filament\Pages\Actions\ButtonAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Pages\Concerns\HasTranslateConfigure;

trait HasResourcePage
{
    use HasTranslateConfigure;

    /**
     * Get the localization path based on the model name
     */
    public static function getLocalePath(): string
    {
        return static::getResource()::getLocalePath();
    }
    /**
     * Get a localized string or return null if not found
     */
    public static function getLocale($key): string | null
    {
        $locale_path = static::getLocalePath();

        if (Lang::has($key = $locale_path . '.' . $key, app()->getLocale())) {
            return __($key);
        }

        return null;
    }

    public static function getNavigationLabel(): string
    {
        return static::getLocale('label.' . Str::snake(class_basename(static::class))) ?? parent::getNavigationLabel();
    }

    public function getTitle(): string | Htmlable
    {
        return static::getLocale('label.' . Str::snake(class_basename(static::class))) ?? parent::getTitle();
    }
}