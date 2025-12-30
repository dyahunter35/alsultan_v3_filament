<?php

namespace App\Filament\Schemas\Components;

use Filament\Schemas\Components\Component;

class ProductPricingTale extends Component
{
    protected string $view = 'filament.schemas.components.product-pricing-tale';

    public static function make(): static
    {
        return app(static::class);
    }
}
