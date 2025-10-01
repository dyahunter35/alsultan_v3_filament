<?php

namespace App\Filament\Pages\Concerns;

use Filament\Tables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

trait HasTableLayoutResource
{
    protected static function getTableLayout(): ?string
    {
        return  null;
    }

    protected static function getDefaultTableLayout(): ?string
    {
        return  'table';
    }

    protected static function getLayoutColumns(): array
    {
        $tableLayout = static::getTableLayout() ?? Cache::rememberForever(Str::of(static::class)->slug('-'), function () {
            return static::getDefaultTableLayout();
        });

        if ($tableLayout == 'table') {
            return static::getTableLayoutColumns();
        }

        return  static::getGridLayoutColumn();
    }

    protected static function getContentGrid(): ?array
    {
        $tableLayout = static::getTableLayout() ?? Cache::rememberForever(Str::of(static::class)->slug('-'), function () {
            return static::getDefaultTableLayout();
        });

        if ($tableLayout == 'table') {
            return null;
        }

        return static::getDefaultTableContentGrid();
    }

    protected static function getDefaultTableContentGrid(): ?array
    {
        return [
            'md' => 2,
            'xl' => 4,
            '2xl' => 5,
        ];
    }

    public static function getLayoutHeaderActions(): array
    {
        if(static::getTableLayout()){
            return [];
        }

        return [
            Tables\Actions\Action::make('table-layout')
                ->icon('heroicon-o-table')
                ->iconButton()
                ->action(fn () => Cache::put(Str::of(static::class)->slug('-'), 'table')),

            Tables\Actions\Action::make('grid-layout')
                ->icon('heroicon-o-view-grid')
                ->iconButton()
                ->action(fn () => Cache::put(Str::of(static::class)->slug('-'), 'grid')),
        ];
    }
}
