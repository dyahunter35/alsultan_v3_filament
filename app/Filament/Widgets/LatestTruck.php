<?php

namespace App\Filament\Widgets;

use App\Enums\TruckState;
use App\Filament\Resources\Trucks\TruckResource;
use App\Models\Truck;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTruck extends BaseWidget
{
    protected int|string|array $columnSpan = '1';
    protected static ?int $sort = 3;
    protected static ?string $heading = 'الشاحنات في الطريق';
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => Truck::out()->where('truck_status', TruckState::OnWay)->latest()->limit(5)) // جلب آخر 10 طلبات
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->copyable() // ميزة إضافية لنسخ الكود بسرعة
                    ->fontFamily('mono')
                    ->icon('heroicon-m-hashtag')
                    ->color('gray'),

                TextColumn::make('car_number')
                    ->label('رقم اللوحة')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('pack_date')
                    ->label('تاريخ الشحن')
                    ->date('Y-m-d')
                    ->description(fn($record) => Carbon::make($record->pack_date)->diffForHumans() ?? '') // إظهار الوقت المنقضي (منذ يومين مثلاً)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true), // إخفاءه افتراضياً لتقليل الزحام

            ])
            ->recordActions([
                    Action::make('view')
                        ->label('عرض')
                        ->url(fn($record): string => TruckResource::getUrl('edit', ['record' => $record])),
                ]);
    }
}
