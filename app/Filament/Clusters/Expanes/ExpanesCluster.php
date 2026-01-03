<?php

namespace App\Filament\Clusters\Expanes;

use App\Filament\Pages\Concerns\HasSinglePage;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class ExpanesCluster extends Cluster
{
    use HasSinglePage;

    protected static ?int $navigationSort = 11;

    public static function getLocalePath(): string
    {
        return 'expense';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('expense.navigation.label');
    }
}
