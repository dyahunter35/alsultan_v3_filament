<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait HasTableGridResource
{
    protected static function getTableContentGrid(): ?array
    {
        $tableLayout = Cache::rememberForever(self::class, function () {
            return 'grid';
        });

        if ($tableLayout == 'table') {
            return null;
        }

        return [
            'md' => 2,
            'xl' => 4,
            '2xl' => 5,
        ];
    }

    protected static function getTableColumns(): array
    {
        $tableLayout = Cache::rememberForever('trip-table-layout', function () {
            return 'grid';
        });


        if ($tableLayout == 'table') {
            return static::getTableLayoutColumns();
        }

        return  static::getGridLayoutColumn();
    }


}
