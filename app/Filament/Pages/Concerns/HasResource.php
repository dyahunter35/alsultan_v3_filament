<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Form;
use Illuminate\Support\Facades\File;

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
    public static function getLocale($key): string | null
    {
        $locale_path = static::getLocalePath();

        if (Lang::has($key = $locale_path . '.' . $key, app()->getLocale())) {
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
    
    /**
     * Automatically generate translation file for this resource
     * This can be called from the resource class to create initial translations
     */
    public static function generateTranslationFile(Form $form, bool $overwrite = false): bool
    {
        $localePath = static::getLocalePath();
        $locale = app()->getLocale();
        $filePath = lang_path($locale . '/' . $localePath . '.php');
        
        // Extract all translatable strings from the form
        $strings = static::extractTranslatableStrings($form);
        
        // Add standard resource translations
        $strings['navigation_group'] = null;
        $strings['label']['model_label'] = static::getModelLabel();
        $strings['label']['plural_model_label'] = static::getPluralModelLabel();
        $strings['label']['list_projects'] = 'Project List';
        $strings['label']['list_project'] = 'Project List';
        $strings['breadcrumb']['index'] = static::getPluralModelLabel();
        $strings['breadcrumb']['list_project'] = 'Project List';
        
        // Check if file exists and we're not overwriting
        if (File::exists($filePath) && !$overwrite) {
            // Merge with existing translations
            $existingStrings = require($filePath);
            $strings = array_replace_recursive($existingStrings, $strings);
        }
        
        // Create the PHP file content
        $fileContent = "<?php\n\nreturn " . var_export($strings, true) . ";\n";
        
        // Make sure the directory exists
        $directory = dirname($filePath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // Write the file
        return File::put($filePath, $fileContent) !== false;
    }
}
