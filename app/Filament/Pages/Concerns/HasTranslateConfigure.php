<?php

namespace App\Filament\Pages\Concerns;

use Filament\Forms;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step as WizardStep;
use Filament\Tables;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait HasTranslateConfigure
{
    protected static array $cachedTranslations = [];

    /**
     * Get a direct translation or generate one automatically from the component properties
     *
     * @param  string  $key  The translation key
     * @param  array  $replacements  Optional parameter replacements
     * @return string|null The translated string or null
     */
    public static function getSmartTranslation(string $key, array $replacements = []): ?string
    {
        $locale_path = static::getLocalePath();
        $fullKey = $locale_path.'.'.$key;

        // Check if translation already exists in language file
        if (Lang::has($fullKey, app()->getLocale())) {
            return __($fullKey, $replacements);
        }

        return null;
    }

    /**
     * Cache translations for performance
     */
    protected static function cacheTranslations(string $locale_path): void
    {
        if (! empty(static::$cachedTranslations[$locale_path])) {
            return;
        }

        $translations = trans($locale_path);
        static::$cachedTranslations[$locale_path] = is_array($translations) ? $translations : [];
    }

    public static function translateConfigureInfolist(): void
    {
        static::cacheTranslations(static::getLocalePath());

        Component::configureUsing(function (Component $component): void {
            // Get component type for special handling
            $componentType = get_class($component);

            // Special handling for Section components
            if ($component instanceof Section) {
                $heading = $component->getHeading();
                if ($heading) {
                    $cleanHeading = (string) str($heading)->snake();
                    if ($localeHeading = static::getSmartTranslation('fields.'.$cleanHeading.'.label')) {
                        $component->heading($localeHeading);
                    }

                    // Also look for a description
                    if (method_exists($component, 'getDescription') && $description = static::getSmartTranslation('form.'.$cleanHeading.'.description')) {
                        $component->description($description);
                    }
                }
            }

            // Special handling for Wizard Steps
            elseif ($component instanceof WizardStep) {
                $label = $component->getLabel();
                if ($label) {
                    $cleanLabel = (string) str($label)->snake();
                    if ($localeLabel = static::getSmartTranslation('fields.'.$cleanLabel.'.label')) {
                        $component->label($localeLabel);
                    }

                    // Also look for a description
                    if (method_exists($component, 'getDescription') && $description = static::getSmartTranslation('fields.'.$cleanLabel.'.description')) {
                        $component->description($description);
                    }
                }
            }
        });

        Entry::configureUsing(function (Entry $component): void {
            $componentType = get_class($component);

            if ($component->getLabel()) {
                $label = (string) str($component->getLabel())
                    ->beforeLast('.')
                    ->afterLast('.')
                    ->kebab()
                    ->replace(['-', '_'], ' ')
                    ->replaceLast(' id', ' ')
                    ->snake();

                if (method_exists($component, 'label')) {
                    if ($localeLabel = static::getSmartTranslation('fields.'.$label.'.label')) {
                        $component->label($localeLabel);
                    }
                }

                if (method_exists($component, 'icon') && $icon = static::getSmartTranslation('fields.'.$label.'.icon')) {
                    $component->icon($icon);
                }

                if (method_exists($component, 'helperText') && $locale_helper_text = static::getSmartTranslation('fields.'.$label.'.helper_text')) {
                    $component->helperText($locale_helper_text);
                }

                if (method_exists($component, 'prefix') && $locale_prefix = static::getSmartTranslation('fields.'.$label.'.prefix')) {
                    $component->prefix($locale_prefix);
                }

                if (method_exists($component, 'suffix') && $locale_suffix = static::getSmartTranslation('fields.'.$label.'.suffix')) {
                    $component->suffix($locale_suffix);
                }

                // Handle options for Select, Radio, Checkbox components
                if (in_array($componentType, [Forms\Components\Select::class, Forms\Components\Radio::class, Forms\Components\Checkbox::class])) {
                    $options = $component->getOptions();
                    if (is_array($options)) {
                        $translatedOptions = [];
                        foreach ($options as $key => $value) {
                            // Attempt to translate each option
                            $optionKey = 'fields.'.$label.'.options.'.Str::slug((string) $key, '_');
                            $translatedOptions[$key] = static::getSmartTranslation($optionKey) ?? $value;
                        }
                        $component->options($translatedOptions);
                    }
                }
            }
            /*  $name = (string) str($component->getHeader())
                ->beforeLast('.')
                ->afterLast('.')
                ->kebab()
                ->replace(['-', '_'], ' ')
                ->replaceLast(' id', ' ')
                ->snake(); */
        });
    }

    public static function translateConfigureForm(): void
    {
        static::cacheTranslations(static::getLocalePath());

        Component::configureUsing(function (Component $component): void {
            // Get component type for special handling
            $componentType = get_class($component);

            // Special handling for Section components
            if ($component instanceof Section) {
                $heading = $component->getHeading();
                if ($heading) {
                    $cleanHeading = (string) str($heading)->snake();
                    if ($localeHeading = static::getSmartTranslation('sections.'.$cleanHeading.'.label')) {
                        $component->heading($localeHeading);
                    }

                    // Also look for a description
                    if (method_exists($component, 'getDescription') && $description = static::getSmartTranslation('sections.'.$cleanHeading.'.description')) {
                        $component->description($description);
                    }

                    if (method_exists($component, 'getIcon') && $icon = static::getSmartTranslation('sections.'.$cleanHeading.'.icon')) {
                        $component->icon($icon);
                    }
                }
            }

            // Repeter
            // Handle Repeater Components and nested fields
            if ($component instanceof \Filament\Forms\Components\Repeater) {
                $repeaterLabel = $component->getLabel();
                if ($repeaterLabel) {
                    $cleanRepeater = (string) str($repeaterLabel)->snake();
                    // dd($cleanRepeater);
                    if ($localeRepeaterLabel = static::getSmartTranslation('fields.'.$cleanRepeater.'.label')) {
                        $component->label($localeRepeaterLabel);
                    }

                    if ($localeRepeaterDesc = static::getSmartTranslation('fields.'.$cleanRepeater.'.description')) {
                        $component->description($localeRepeaterDesc);
                    }
                }
                // dd($component->getChildComponents());
                // Loop over nested fields inside the repeater
                /*  foreach ($component->getItems() as $nestedField) {
                    if ($nestedField instanceof \Filament\Forms\Components\Field) {
                        $fieldName = (string) str($nestedField->getName())->snake();
                        $translationBase = 'fields.' . $cleanRepeater . '.fields.' . $fieldName;

                        if ($localeLabel = static::getSmartTranslation($translationBase . '.label')) {
                            $nestedField->label($localeLabel);
                        }
                        if ($localePlaceholder = static::getSmartTranslation($translationBase . '.placeholder')) {
                            $nestedField->placeholder($localePlaceholder);
                        }
                        if ($localeHelper = static::getSmartTranslation($translationBase . '.helper_text')) {
                            $nestedField->helperText($localeHelper);
                        }

                        // Optional: handle options
                        if (method_exists($nestedField, 'options')) {
                            $options = $nestedField->getOptions();
                            if (is_array($options)) {
                                $translatedOptions = [];
                                foreach ($options as $key => $value) {
                                    $optionKey = $translationBase . '.options.' . Str::slug((string) $key, '_');
                                    $translatedOptions[$key] = static::getSmartTranslation($optionKey) ?? $value;
                                }
                                $nestedField->options($translatedOptions);
                            }
                        }
                    }
                } */
            }

            // Special handling for Wizard Steps
            elseif ($component instanceof WizardStep) {
                $label = $component->getLabel();
                if ($label) {
                    $cleanLabel = (string) str($label)->snake();
                    if ($localeLabel = static::getSmartTranslation('fields.'.$cleanLabel.'.label')) {
                        $component->label($localeLabel);
                    }

                    // Also look for a description
                    if (method_exists($component, 'getDescription') && $description = static::getSmartTranslation('fields.'.$cleanLabel.'.description')) {
                        $component->description($description);
                    }
                }
            }

            // Special handling for Wizard Steps
            elseif ($component instanceof Tab) {
                $label = $component->getLabel();
                if ($label) {
                    $cleanLabel = (string) str($label)->snake();
                    if ($localeLabel = static::getSmartTranslation('tabs.'.$cleanLabel.'.label')) {
                        $component->label($localeLabel);
                    }

                    // Also look for a description
                    if (method_exists($component, 'getDescription') && $description = static::getSmartTranslation('tabs.'.$cleanLabel.'.description')) {
                        $component->description($description);
                    }
                    if (method_exists($component, 'getIcon') && $icon = static::getSmartTranslation('tabs.'.$cleanLabel.'.icon')) {
                        $component->icon($icon);
                    }
                }
            }
        });

        Field::configureUsing(function (Field $component): void {
            $componentType = get_class($component);

            if ($component->getLabel()) {
                $label = (string) str($component->getLabel())
                    ->beforeLast('.')
                    ->afterLast('.')
                    ->kebab()
                    ->replace(['-', '_'], ' ')
                    ->replaceLast(' id', ' ')
                    ->snake();

                if (method_exists($component, 'label')) {
                    if ($localeLabel = static::getSmartTranslation('fields.'.$label.'.label')) {
                        $component->label($localeLabel);
                    }
                }

                if (method_exists($component, 'description') && $locale_description = static::getSmartTranslation('fields.'.$label.'.description')) {
                    $component->description($locale_description);
                }

                if (method_exists($component, 'placeholder') && $locale_placeholder = static::getSmartTranslation('fields.'.$label.'.placeholder')) {
                    $component->placeholder($locale_placeholder);
                }

                if (method_exists($component, 'helperText') && $locale_helper_text = static::getSmartTranslation('fields.'.$label.'.helper_text')) {
                    $component->helperText($locale_helper_text);
                }

                if (method_exists($component, 'prefix') && $locale_prefix = static::getSmartTranslation('fields.'.$label.'.prefix')) {
                    $component->prefix($locale_prefix);
                }

                if (method_exists($component, 'suffix') && $locale_suffix = static::getSmartTranslation('fields.'.$label.'.suffix')) {
                    $component->suffix($locale_suffix);
                }

                // Handle options for Select, Radio, Checkbox components
                if (in_array($componentType, [Forms\Components\Select::class, Forms\Components\Radio::class, Forms\Components\Checkbox::class])) {
                    $options = $component->getOptions();
                    if (is_array($options)) {
                        $translatedOptions = [];
                        foreach ($options as $key => $value) {
                            // Attempt to translate each option
                            $optionKey = 'fields.'.$label.'.options.'.Str::slug((string) $key, '_');
                            $translatedOptions[$key] = static::getSmartTranslation($optionKey) ?? $value;
                        }
                        $component->options($translatedOptions);
                    }
                }
            }
            /*  $name = (string) str($component->getHeader())
                ->beforeLast('.')
                ->afterLast('.')
                ->kebab()
                ->replace(['-', '_'], ' ')
                ->replaceLast(' id', ' ')
                ->snake(); */
        });
    }

    public static function translateConfigureTable(): void
    {
        Tables\Columns\Column::configureUsing(function (Tables\Columns\Column $component) {
            $label = (string) Str::of($component->getName())
                ->beforeLast('.')
                ->afterLast('.')
                ->kebab()
                ->replace(['-', '_'], ' ')
                ->replaceLast(' id', ' ')
                ->snake();

            if (method_exists($component, 'label')) {
                if ($localeLabel = static::getSmartTranslation('fields.'.$label.'.label')) {
                    $component->label($localeLabel);
                }
            }

            if (method_exists($component, 'description') && $locale_description = static::getSmartTranslation('fields.'.$label.'.description')) {
                $component->description($locale_description);
            }

            if (method_exists($component, 'prefix') && $locale_prefix = static::getSmartTranslation('fields.'.$label.'.prefix')) {
                $component->prefix($locale_prefix);
            }
        });
    }

    public static function multiLanguageFormComponent(Forms\Components\Component $formComponent, array $languages = ['ar', 'en']): array
    {
        $name = $formComponent->getName();

        return [
            Forms\Components\Tabs::make($name.'_tab')
                ->tabs(
                    collect($languages)->map(fn ($language) => Forms\Components\Tabs\Tab::make($language.'tabs')
                        ->label(__('language.'.$language))
                        ->schema([
                            (clone $formComponent)->name($name.'.'.$language)
                                ->statePath($name.'.'.$language),
                        ]))->toArray()
                ),
        ];
    }

    /**
     * Extract all translatable strings from a form
     * Can be used to automatically generate translation files
     */
    public static function extractTranslatableStrings(Forms\Form $form): array
    {
        $strings = [];

        $extractFromComponent = function ($component) use (&$strings, &$extractFromComponent) {
            // Get basic component info
            if (method_exists($component, 'getLabel')) {
                $label = $component->getLabel();
                if ($label) {
                    $cleanLabel = (string) str($label)->snake();

                    // Add to strings array
                    $strings['fields'][$cleanLabel]['label'] = $label;

                    // Get helper text if available
                    if (method_exists($component, 'getHelperText')) {
                        $helperText = $component->getHelperText();
                        if ($helperText) {
                            $strings['fields'][$cleanLabel]['helper_text'] = $helperText;
                        }
                    }

                    // // Get placeholder if available
                    // if (method_exists($component, 'getPlaceholder')) {
                    //     $placeholder = $component->getPlaceholder();
                    //     if ($placeholder) {
                    //         $strings['fields'][$cleanLabel]['placeholder'] = $placeholder;
                    //     }
                    // }

                    // Get description if available
                    if (method_exists($component, 'getDescription')) {
                        $description = $component->getDescription();
                        if ($description) {
                            $strings['fields'][$cleanLabel]['description'] = $description;
                        }
                    }

                    // // Get options if available
                    // if (method_exists($component, 'getOptions')) {
                    //     $options = $component->getOptions();
                    //     if (is_array($options)) {
                    //         foreach ($options as $key => $value) {
                    //             $strings['fields'][$cleanLabel][$key] = $value;
                    //         }
                    //     }
                    // }
                }
            }

            // Handle specific components
            if ($component instanceof Section && method_exists($component, 'getHeading')) {
                $heading = $component->getHeading();
                if ($heading) {
                    $cleanHeading = (string) str($heading)->snake();
                    $strings['fields'][$cleanHeading]['label'] = $heading;

                    if (method_exists($component, 'getDescription')) {
                        $description = $component->getDescription();
                        if ($description) {
                            $strings['fields'][$cleanHeading]['description'] = $description;
                        }
                    }
                }
            }

            if ($component instanceof Step && method_exists($component, 'getLabel')) {
                $stepLabel = $component->getLabel();
                if ($stepLabel) {
                    $cleanLabel = (string) str($stepLabel)->snake();
                    $strings['fields'][$cleanLabel]['label'] = $stepLabel;

                    if (method_exists($component, 'getDescription')) {
                        $description = $component->getDescription();
                        if ($description) {
                            $strings['fields'][$cleanLabel]['description'] = $description;
                        }
                    }
                }
            }

            // Process children if they exist
            if (method_exists($component, 'getChildComponents')) {
                $children = $component->getChildComponents();
                foreach ($children as $child) {
                    $extractFromComponent($child);
                }
            }

            return $strings;
        };

        // Process all components in the form
        foreach ($form->getComponents() as $component) {
            $extractFromComponent($component);
        }

        return $strings;
    }
}
