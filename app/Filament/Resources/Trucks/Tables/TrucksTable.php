<?php

namespace App\Filament\Resources\Trucks\Tables;

use App\Enums\Country;
use App\Enums\StockCase;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TrucksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Truck::where('type', TruckType::Outer))
            ->columns([

                Tables\Columns\TextColumn::make('driver_name')
                    ->getStateUsing(fn($record) =>  $record->driver_name . '<br>' . $record->driver_phone)->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('car_number')
                    ->getStateUsing(fn($record) =>  $record->truck_model . '<br>' . $record->car_number)->html()

                    ->searchable(),
                Tables\Columns\TextColumn::make('pack_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('truck_status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->badge()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('contractorInfo.name')
                    ->label(__('truck.fields.contractor_id.label'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('companyId.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from.name')
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
                Actions\Action::make('report')->action(fn(Truck $record) => redirect(TruckReport::getUrl(['truckId' => $record->id]))),
                Actions\Action::make('unload_cargo')
                    ->label('تنزيل للمخزن')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    // 1. تعبئة النموذج بالبيانات الموجودة مسبقاً في الشاحنة
                    ->fillForm(fn(Truck $record): array => [
                        'cargos' => $record->cargos->map(function ($cargo) {
                            return [
                                'id' => $cargo->id,
                                'product_id' => $cargo->product_id,
                                'quantity' => $cargo->quantity,
                                'real_quantity' => $cargo->real_quantity, // في حال تم إدخالها سابقاً
                            ];
                        })->toArray(),
                        'brunch_id' => $record->branch_to,
                        'arrive_date' => $record->arrive_date,
                    ])
                    // 2. تصميم النافذة (Modal)
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                Select::make('brunch_id')
                                    ->label('المخزن الوجهة')
                                    ->options(Branch::pluck('name', 'id'))
                                    ->required(),

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
                                    ->columns(3) // تنسيق العرض في 3 أعمدة
                            ])

                    ])
                    // 3. معالجة البيانات بعد الضغط على زر الحفظ
                    ->action(function (array $data, Truck $record) {

                        $inventoryService = app(InventoryService::class);
                        $targetBranch = Branch::find($data['brunch_id']);
                        $causer = auth()->user();

                        if (!$targetBranch) {
                            Notification::make()->title('خطأ: لم يتم تحديد المخزن.')->danger()->send();
                            return;
                        }

                        foreach ($data['cargos'] as $item) {
                            $productId = $item['product_id'];
                            $expectedQty = (float)$item['quantity'];
                            $realQtyInput = (float)$item['real_quantity'];

                            // تحديد الكمية النهائية التي ستدخل المخزن
                            // (إذا كان real_quantity غير فارغ و أكبر من 0، استخدمه، وإلا استخدم الكمية المسجلة)
                            $quantityToMove = ($realQtyInput > 0) ? $realQtyInput : $expectedQty;

                            if ($quantityToMove <= 0) {
                                continue;
                            }

                            // تحديث سجل الـ truck_cargos بالكمية الفعلية المدخلة
                            $cargoModel = TruckCargo::find($item['id']);
                            if ($cargoModel) {
                                $cargoModel->update(['real_quantity' => $realQtyInput]);
                            }

                            // استدعاء خدمة المخزون لنقل الكمية
                            $product = Product::find($productId);

                            if ($product) {
                                try {
                                    $inventoryService->addStockForBranch(
                                        product: $product,
                                        branch: $targetBranch,
                                        quantity: $quantityToMove,
                                        type: StockCase::Increase,
                                        notes: "وصول شحنة رقم #{$record->id} إلى {$targetBranch->name}",
                                        causer: $causer,
                                        truck: $record
                                    );
                                } catch (Exception $e) {
                                    Notification::make()
                                        ->title("فشل نقل المنتج {$product->name}")
                                        ->body("الرجاء التحقق من سجل الأخطاء.")
                                        ->danger()
                                        ->send();
                                }
                            }
                        }

                        // تحديث حالة الشاحنة
                        $record->update([
                            'truck_status' => TruckState::reach,

                            'arrive_date' => $data['arrive_date']
                        ]);

                        Notification::make()
                            ->title('تمت عملية الجرد ونقل البضائع للمخزن بنجاح')
                            ->success()
                            ->send();
                    }),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->groupedBulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
