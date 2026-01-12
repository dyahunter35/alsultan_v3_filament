<?php

namespace App\Filament\Resources\InnerTrucks\Tables;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use App\Filament\Pages\Reports\TruckReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Truck;
use App\Models\TruckCargo;
use App\Services\InventoryService;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TrucksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Truck::where('type', TruckType::Local))
            ->columns([

                Tables\Columns\TextColumn::make('driver_name')
                    ->getStateUsing(fn ($record) => $record->driver_name.'<br>'.$record->driver_phone)->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('car_details')
                    ->getStateUsing(fn ($record) => $record->truck_model.'<br>'.$record->car_number)->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pack_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('truck_status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('from.name')
                    ->label(__('truck.fields.from_branch.label'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toBranch.name')
                    ->label(__('truck.fields.to.label')),
                Tables\Columns\TextColumn::make('arrive_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_converted')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('toStore')
                    ->label(__('truck.filters.toStore.label'))
                    ->relationship('toBranch', 'name'),

                SelectFilter::make('country')
                    ->label(__('truck.filters.country.label'))
                    ->options(Country::class),

                DateRangeFilter::make('pack_date')
                    ->label(__('truck.filters.pack_date.label'))->opens(OpenDirection::RIGHT),
                DateRangeFilter::make('arrive_date')
                    ->label(__('truck.filters.arrive_date.label'))->opens(OpenDirection::RIGHT),

            ])
            ->recordActions([
                Actions\ActionGroup::make([

                    Actions\Action::make('report')
                        ->label(__('truck.actions.report.label'))
                        ->icon(__('truck.actions.report.icon'))
                        ->action(fn (Truck $record) => redirect(TruckReport::getUrl(['truckId' => $record->id]))),

                    Actions\Action::make('reload_cargo')
                        ->requiresConfirmation()
                        ->modalDescription(__('truck.actions.reload_cargo.message'))
                        ->label(__('truck.actions.reload_cargo.label'))
                        ->icon('heroicon-m-truck')
                        ->color('danger')
                        ->action(function (Truck $record) {
                            // إعادة تعيين حالة الشاحنة إلى "في الطريق"
                            $record->stockHistory()->delete();

                            $record->update([
                                'is_converted' => 0,
                            ]);

                            Notification::make()
                                ->title('تمت إعادة تحميل الحمولة بنجاح')
                                ->success()
                                ->send();
                        })->visible(fn (Truck $record) => $record->is_converted),

                    Actions\Action::make('unload_cargo')
                        ->label(__('truck.actions.unload_cargo.label'))
                        ->label(__('truck.actions.unload_cargo.label'))
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        // 1. تعبئة النموذج بالبيانات الموجودة مسبقاً في الشاحنة
                        ->fillForm(fn (Truck $record): array => [
                            'cargos' => $record->cargos->map(function ($cargo) {
                                return [
                                    'id' => $cargo->id,
                                    'product_id' => $cargo->product_id,
                                    'quantity' => $cargo->quantity,
                                    'real_quantity' => $cargo->real_quantity, // في حال تم إدخالها سابقاً
                                ];
                            })->toArray(),
                            'branch_to' => $record->branch_to,
                            'arrive_date' => $record->arrive_date,
                        ])
                        // 2. تصميم النافذة (Modal)
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    Select::make('branch_to')
                                        ->label('المخزن الوجهة')
                                        ->options(function (Truck $record) {
                                            // جلب جميع المخازن ماعدا المخزن الذي خرجت منه الشاحنة
                                            return Branch::query()
                                                ->where('id', '!=', $record->from_id) // استبدل branch_from بالاسم الصحيح للحقل في جدولك
                                                ->pluck('name', 'id');
                                        })->required(),

                                    DatePicker::make('arrive_date')
                                        ->required(),
                                    Repeater::make('cargos')
                                        ->label('قائمة البضائع على الشاحنة')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->columnSpanFull()
                                        ->reorderable(false)
                                        ->grid(2)
                                        ->schema([
                                            // عرض اسم المنتج (للقراءة فقط)
                                            Select::make('product_id')
                                                ->label('المنتج')
                                                ->options(Product::pluck('name', 'id'))
                                                ->disabled()
                                                ->dehydrated(), // مهم جداً لإرسال القيمة حتى لو كان الحقل معطل

                                            // الكمية المسجلة (المتوقعة)
                                            TextInput::make('quantity')
                                                ->label('الكمية المسجلة')
                                                ->readOnly()
                                                ->dehydrated(),

                                            // الكمية الفعلية (يكتبها المستخدم)
                                            TextInput::make('real_quantity')
                                                ->label('الكمية الفعلية')
                                                ->helperText('اتركه فارغاً إذا كانت الكمية مطابقة')
                                                ->numeric(),

                                            // حقل مخفي لتمرير الـ ID الخاص بالبضاعة
                                            Hidden::make('id'),
                                        ])
                                        ->columns(3), // تنسيق العرض في 3 أعمدة
                                ]),

                        ])
                        // 3. معالجة البيانات بعد الضغط على زر الحفظ
                        ->action(function (array $data, Truck $record) {
                            $inventoryService = app(InventoryService::class);

                            // المخزن المصدر (من بيانات الشاحنة) والمخزن الهدف (من النموذج)
                            $sourceBranch = Branch::find($record->from_id); // تأكد من اسم الحقل في جدول الشاحنات
                            $targetBranch = Branch::find($data['branch_to']);

                            $causer = auth()->user();

                            if (! $sourceBranch || ! $targetBranch) {
                                Notification::make()->title('خطأ في تحديد المخازن')->danger()->send();

                                return;
                            }

                            foreach ($data['cargos'] as $item) {
                                $productId = $item['product_id'];
                                $expectedQty = (float) $item['quantity'];
                                $realQtyInput = (float) $item['real_quantity'];

                                $quantityToMove = ($realQtyInput > 0) ? $realQtyInput : $expectedQty;

                                if ($quantityToMove <= 0) {
                                    continue;
                                }

                                $product = Product::find($productId);

                                if ($product) {
                                    try {
                                        // تنفيذ عملية التحويل المزدوجة
                                        $inventoryService->transferStock(
                                            product: $product,
                                            fromBranch: $sourceBranch,
                                            toBranch: $targetBranch,
                                            quantity: $quantityToMove,
                                            notes: "تحويل بضاعة عبر شاحنة رقم #{$record->id}",
                                            causer: $causer,
                                            truck: $record
                                        );

                                        // تحديث الكمية الفعلية في سجل الشحنة
                                        TruckCargo::find($item['id'])?->update(['real_quantity' => $quantityToMove]);
                                    } catch (Exception $e) {
                                        Notification::make()->title("خطأ أثناء تحويل {$product->name}")->danger()->send();
                                    }
                                }
                            }

                            // تحديث حالة الشاحنة لتصبح "وصلت" ومحولة
                            $record->update([
                                'truck_status' => TruckState::reach,
                                'arrive_date' => $data['arrive_date'],
                                'is_converted' => 1,
                            ]);

                            Notification::make()->title('تم تحويل البضائع بين المخازن بنجاح')->success()->send();
                        })
                        ->visible(fn (Truck $record) => ! $record->is_converted),

                    // Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                ]),

            ])
            ->groupedBulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
