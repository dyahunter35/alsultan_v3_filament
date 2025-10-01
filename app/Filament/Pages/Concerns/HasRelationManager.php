<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Model;

trait HasRelationManager
{
    use HasTranslateConfigure;

    public static function getLocalePath(): string
    {
        if (isset(static::$localePath)) {
            return static::$localePath;
        } else {
            return Str::of(static::$relationship)->snake()->singular();
        }
    }

    public static function getLocale($key): string | null
    {
        $locale_path = static::getLocalePath();

        if (!$locale_path) {
            return null;
        }

        if (Lang::has($key = $locale_path . '.' . $key, app()->getLocale())) {
            return __($key);
        }

        return null;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __(static::$title) ?? (static::getLocale('label.plural') ?? __(Str::title(static::getPluralRecordLabel())));
    }

    protected static function getPluralRecordLabel(): ?string
    {
        return __(static::$pluralModelLabel) ?? (static::getLocale('label.plural') ?? __((string) Str::of(static::getRelationshipName())
            ->kebab()
            ->replace('-', ' ')));
    }

    protected static function getRecordLabel(): ?string
    {
        return __(static::$modelLabel) ?? (static::getLocale('label.single'));
    }
}
