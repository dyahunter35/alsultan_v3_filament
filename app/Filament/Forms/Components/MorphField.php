<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Contracts\Support\Htmlable;

class MorphField extends Fieldset
{
    protected string $baseName;
    protected array $models = [];

    public static function make(string | Htmlable | Closure | null $label = null): static
    {
        $static = new static($label);
        $static->baseName = $label;

        return $static;
    }

    public function models(array $models): static
    {
        $this->models = $models;
        return $this;
    }

    public function setUp(): void
    {
        parent::setUp();

        $name = $this->baseName;

        $this->label(false);

        $this->schema([
            MorphSelect::make("{$name}_select")
                ->label(__('اختر الحساب'))
                ->models($this->models),

            Hidden::make("{$name}_id"),
            Hidden::make("{$name}_type"),
        ]);
    }
}
