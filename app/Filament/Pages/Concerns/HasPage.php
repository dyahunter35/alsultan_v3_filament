<?php

namespace App\Filament\Pages\Concerns;

use Filament\Resources\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;

trait HasPage
{
    use HasTranslateConfigure;

    public static function getLocalePath(): string
    {
        if (isset(static::$resource::$localePath)) {
            return static::$localePath;
        } else {
            return 'locale/' . Str::of(class_basename(static::$resource::getModel()))->snake();
        }
    }

    public static function getLocale($key): string | null
    {
        $localePath = static::getLocalePath();

        if (trans()->has($locale = $localePath . '.' . $key, [], app()->getLocale())) {
            return trans($locale);
        }

        return null;
    }
}
