<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int|string|array $columnSpan = '1';

    protected static ?int $sort = 3;
    protected static ?string $heading = 'أحدث الطلبات';

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery()->latest()->limit(10)) // جلب آخر 10 طلبات
            ->columns([
                TextColumn::make('number')
                    ->label('رقم الطلب'),
                TextColumn::make('customer.name')
                    ->label('العميل'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->date(),
            ])
            ->recordActions([
                    Action::make('view')
                        ->label('عرض')
                        ->url(fn($record): string => OrderResource::getUrl('view', ['record' => $record])),
                ]);
    }
}
