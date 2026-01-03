<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashoardLinks extends Widget
{
    protected string $view = 'filament.widgets.dashoard-links';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;
}
