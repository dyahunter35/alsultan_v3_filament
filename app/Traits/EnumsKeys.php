<?php

namespace App\Traits;

use App\Enums\Group;

trait EnumsKeys
{
    public static function getKeys(): array
    {

        $cases = self::cases();
        if (empty($cases)) {
            return [];
        }

        // Check if the first case has a 'value' property
        if (property_exists($cases[0], 'value')) {
            return array_column($cases, 'value');
        }

        // If it's a pure enum, 'values' would be the same as 'names'
        // or you might throw an exception/return an empty array depending on desired behavior.
        // For this trait, we'll return names if it's not backed.
        return self::names();
    }

    public static function getKeyValueArray(): array
    {
        return array_map(fn($case) => [
            'key' => $case->value,
            'label' => $case->getLabel()?? $case->value,
        ], self::cases());
    }

    //get All Labels
    public static function getLabels(): array
    {
        return array_map(fn($case) => $case->getLabel(), self::cases());
    }

    public static function getAllLabels(): array
    {
        return array_combine(array_map(fn($enum) => $enum->name, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases()));
    }

    public static function getString(): string
    {
        return implode(',', self::getKeys());
    }


    /// Grouped
    public static function getGrouped(): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $reflection = new \ReflectionEnumUnitCase(self::class, $case->name);
            $attributes = $reflection->getAttributes(Group::class);

            if (!empty($attributes)) {
                $groupName =  $attributes[0]->newInstance()->group;
                //$groupName = (app()->getLocale() == 'ar') ? $groupName : 'aaaaa';
                $grouped[$groupName][$case->value] = $case->getLabel();
            }
        }

        return $grouped;
    }

    public static function getGroupName(string $group): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $reflection = new \ReflectionEnumUnitCase(self::class, $case->name);
            $attributes = $reflection->getAttributes(Group::class);

            if (!empty($attributes)) {
                $groupName =  $attributes[0]->newInstance()->group;
                if($groupName == $group)
                //$groupName = (app()->getLocale() == 'ar') ? $groupName : 'aaaaa';
                $grouped[]=$case->value ;
            }
        }

        return $grouped;
    }
}
