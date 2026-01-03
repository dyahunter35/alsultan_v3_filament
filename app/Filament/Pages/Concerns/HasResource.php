<?php

namespace App\Filament\Pages\Concerns;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait HasResource
{
    use HasTranslateConfigure;

    public static ?string $localePath = null;

    /**
     * Check if sidebar is collapsible
     */
    private static function checkIsSidebarCollapsible()
    {
        if (isset(static::$isSidebarCollapsible)) {
            config()->set('filament.layout.sidebar.is_collapsible_on_desktop', static::$isSidebarCollapsible);
        }
    }

    /**
     * Get the localization path based on the model name
     */
    public static function getLocalePath(): string
    {
        return static::$localePath ?? Str::of(class_basename(static::getModel()))->snake();
    }

    /**
     * Get a localized string or return null if not found
     */
    public static function getLocale($key): ?string
    {
        $locale_path = static::getLocalePath();

        if (Lang::has($key = $locale_path.'.'.$key, app()->getLocale())) {
            return __($key);
        }

        return null;
    }

    /**
     * Get breadcrumb with translation
     */
    public static function getBreadcrumb(): string
    {
        static::checkIsSidebarCollapsible();

        return static::getLocale('breadcrumb.index') ?? parent::getBreadcrumb();
    }

    /**
     * Get plural model label with translation
     */
    public static function getPluralModelLabel(): string
    {
        return static::getLocale('navigation.plural_label') ?? parent::getPluralModelLabel();
    }

    /**
     * Get model label with translation
     */
    public static function getModelLabel(): string
    {
        return static::getLocale('navigation.model_label') ?? parent::getModelLabel();
    }

    /**
     * Get navigation group with translation
     */
    public static function getNavigationGroup(): ?string
    {
        return static::getLocale('navigation.group') ?? parent::getNavigationGroup();
    }

    /**
     * Get global search result URL
     */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        if (static::canView($record)) {
            return static::getUrl('view', ['record' => $record]);
        }

        if (static::canEdit($record)) {
            return static::getUrl('edit', ['record' => $record]);
        }

        return null;
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return static::getLocale('navigation.icon') ??
            static::$navigationIcon;
    }

    /* public static function getNavigationSort(): ?int
    {
        return static::getLocale('navigation.sort');
    } */
}
