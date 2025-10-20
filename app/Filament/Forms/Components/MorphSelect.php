<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class MorphSelect extends Select
{
    protected ?string $idField = null;
    protected ?string $typeField = null;
    protected array|Closure $models = [];

    public function setMorphFields(string $idField, string $typeField): static
    {
        $this->idField = $idField;
        $this->typeField = $typeField;

        return $this;
    }

    public function models(array|Closure $models): static
    {
        $this->models = $models;
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->detectMorphFields();
        $this->dehydrated(false);
        $this->searchable();

        $this->setUpOptions();
        $this->reactive();

        // Hydrate state من id & type عند التحديث
        $this->afterStateHydrated(function (MorphSelect $component, $state, callable $get) {
            if (!$state) {
                $id = $get($this->getIdField());
                $type = $get($this->getTypeField());

                if ($id && $type) {
                    $prefix = $this->getPrefixFromType($type);
                    $component->state("{$prefix}_{$id}");
                }
            }
        });

        $this->afterStateUpdated(function ($state, callable $set) {
            if (!$state) return;
            foreach ($this->getModels() as $prefix => $class) {
                if (str_starts_with($state, "{$prefix}_")) {
                    $set($this->getIdField(), (int) str_replace("{$prefix}_", '', $state));
                    $set($this->getTypeField(), $class);

                    break;
                }
            }
        });
    }

    protected function getModels(): array
    {
        $models = is_callable($this->models) ? call_user_func($this->models) : $this->models;

        // لو returned array من Closure فيها Builder أو Collection، نرجع فقط اسم الكلاس
        foreach ($models as $prefix => $model) {
            if ($model instanceof \Closure) {
                // نحاول نحتفظ باسم الكلاس الأصلي (مثلاً من الـ Builder)
                $builder = $model();
                if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
                    $models[$prefix] = $builder->getModel()::class;
                } elseif ($builder instanceof \Illuminate\Database\Eloquent\Collection && $builder->first()) {
                    $models[$prefix] = get_class($builder->first());
                }
            }
        }

        return $models;
    }

    protected function detectMorphFields(): void
    {
        $name = $this->getName();
        $base = str_ends_with($name, '_select') ? substr($name, 0, -7) : $name;

        if (!$this->idField) $this->idField = "{$base}_id";
        if (!$this->typeField) $this->typeField = "{$base}_type";
    }

    protected function setUpOptions(): void
    {
        $this->options(function () {
            $options = [];
            foreach ($this->getModels() as $prefix => $model) {

                if (is_callable($model)) {
                    $records = $model();
                    if ($records instanceof Builder) {
                        $records = $records->select('id', 'name', 'permanent')->get();
                    }
                } else {
                    $records = $model::query()->select('id', 'name', 'permanent')->get();
                }

                $mapped = $records->mapWithKeys(function ($record) use ($prefix) {
                    $label = $record->name;

                    // لو Customer، أضف النوع بين قوسين
                    if (property_exists($record, 'permanent')) {
                        $label .= " ({$record->permanent})";
                    }

                    $icon = $prefix === 'user' ? '👤 ' : '💼 ';
                    return ["{$prefix}_{$record->id}" => $icon . $label];
                })->toArray();

                $options = array_merge($options, $mapped);
            }

            return $options;
        });
    }

    protected function getIdField(): string
    {
        return $this->idField;
    }

    protected function getTypeField(): string
    {
        return $this->typeField;
    }

    protected function getPrefixFromType(string $type): string
    {
        $models = $this->getModels();
        foreach ($models as $prefix => $class) {
            if ($class === $type) return $prefix;
        }
        return '';
    }
}
