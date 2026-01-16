<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\TruckState;
use App\Filament\Resources\InnerTrucks\InnerTruckResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Supplyings\SupplyingResource;
use App\Filament\Resources\Trucks\TruckResource;
use Filament\Widgets\Widget;

class DashoardLinks extends Widget
{
    protected string $view = 'filament.widgets.dashoard-links';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getActions(): array
    {
        return [
            [
                'title' => 'فاتورة مبيعات',
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'success',
                'url' => OrderResource::getUrl('create'),
                'value' => \App\Models\Order::whereIn('status', [OrderStatus::New , OrderStatus::Processing])->count(), // عداد حقيقي
            ],
            [
                'title' => 'توريدة جديدة',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'primary',
                'url' => SupplyingResource::getUrl('create'),
                'value' => null,
            ],
            [
                'title' => 'الشحن الخارجي',
                'icon' => 'heroicon-o-truck',
                'color' => 'gray',
                'url' => TruckResource::getUrl('index'),
                'value' => \App\Models\Truck::out()->where('truck_status', TruckState::OnWay)->count(), // عداد حقيقي
            ],
            [
                'title' => 'الشحن الداخلي',
                'icon' => 'heroicon-o-truck',
                'color' => 'gray',
                'url' => InnerTruckResource::getUrl('index'),
                'value' => \App\Models\Truck::local()->where('truck_status', TruckState::OnWay)->count(), // عداد حقيقي
            ],
            [
                'title' => 'الطلبات المعلقة',
                'icon' => 'heroicon-o-clock',
                'color' => 'danger',
                'url' => OrderResource::getUrl('index', ['tab' => 'processing']),
                'value' => \App\Models\Order::where('status', OrderStatus::Processing)->count(), // عداد حقيقي
            ],
        ];
    }
}
