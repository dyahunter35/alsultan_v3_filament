<?php

namespace App\Traits;

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
}
