<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
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
            if (! $state) {
                $id = $get($this->getIdField());
                $type = $get($this->getTypeField());

                if ($id && $type) {
                    // إذا كان Closure، حوله إلى اسم الكلاس الصحيح
                    foreach ($this->getModels() as $prefix => $model) {
                        if ($model instanceof \Closure) {
                            $result = $model();
                            if ($result instanceof \Illuminate\Database\Eloquent\Collection && $result->first()) {
                                $model = get_class($result->first());
                            } elseif ($result instanceof \Illuminate\Database\Eloquent\Builder) {
                                $model = $result->getModel()::class;
                            }
                        }

                        if ($model === $type) {
                            $component->state("{$prefix}_{$id}");
                            break;
                        }
                    }
                }
            }
        });

        $this->afterStateUpdated(function ($state, callable $set) {
            if (! $state) {
                return;
            }

            foreach ($this->getModels() as $prefix => $class) {
                // لو Class عبارة عن Closure
                if ($class instanceof \Closure) {
                    $builderOrCollection = $class();
                    if ($builderOrCollection instanceof \Illuminate\Database\Eloquent\Builder) {
                        $class = $builderOrCollection->getModel()::class;
                    } elseif ($builderOrCollection instanceof \Illuminate\Database\Eloquent\Collection && $builderOrCollection->first()) {
                        $class = get_class($builderOrCollection->first());
                    }
                }

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
        return is_callable($this->models)
            ? call_user_func($this->models)
            : $this->models;
    }

    protected function detectMorphFields(): void
    {
        $name = $this->getName();
        $base = str_ends_with($name, '_select') ? substr($name, 0, -7) : $name;

        if (! $this->idField) {
            $this->idField = "{$base}_id";
        }
        if (! $this->typeField) {
            $this->typeField = "{$base}_type";
        }
    }

    protected function setUpOptions(): void
    {
        $this->options(function () {
            $options = [];
            foreach ($this->getModels() as $prefix => $model) {

                if (is_callable($model)) {
                    $records = $model();
                    if ($records instanceof Builder) {
                        $records = $records->select('id', 'name')->get();
                    }
                } else {
                    $records = $model::query()->select('id', 'name')->get();
                }

                $mapped = $records->mapWithKeys(function ($record) use ($prefix) {
                    $label = $record->name;

                    // لو Customer، أضف النوع بين قوسين
                    if (property_exists($record, 'permanent')) {
                        $label .= " ({$record->permanent})";
                    }

                    $icon = $prefix !== 'customer' ? '👤 ' : '💼 ';

                    return ["{$prefix}_{$record->id}" => $icon.$label];
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
            if ($class === $type) {
                return $prefix;
            }
        }

        return '';
    }
}
