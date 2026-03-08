<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\StockCase;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Product;
use App\Services\InventoryService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Collection;

class Edit2Order extends EditRecord
{
    protected static string $resource = OrderResource::class;

    // تخزين الحالة القديمة قبل التعديل
    protected Collection $oldItems;
    protected ?OrderStatus $oldStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * يتنفذ قبل الحفظ — نحفظ هنا snapshot للبيانات القديمة للمقارنة لاحقاً
     */
    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        // حفظ الحالة القديمة
        $this->oldStatus = $record->status;

        // حفظ العناصر القديمة مفهرسة بالـ product_id
        $this->oldItems = $record->items->map(fn($item) => [
            'product_id' => (int) $item->product_id,
            'qty' => (float) $item->qty,
        ])->keyBy('product_id');
    }

    /**
     * يتنفذ بعد الحفظ — نطبق هنا منطق المخزون بناءً على الفروقات
     */
    protected function afterSave(): void
    {
        $record = $this->getRecord()->refresh();
        $inventoryService = new InventoryService;
        $currentBranch = Filament::getTenant();
        $currentUser = auth()->user();

        $newStatus = $record->status;

        // جلب العناصر الجديدة بعد الحفظ مفهرسة بالـ product_id
        $newItems = $record->items->map(fn($item) => [
            'product_id' => (int) $item->product_id,
            'qty' => (float) $item->qty,
        ])->keyBy('product_id');

        // تحديد إذا كان الطلب فعّالاً (أي له أثر على المخزون) قبل وبعد التعديل
        // فقط الحالة Cancelled لا تخصم مخزوناً
        $wasDeducting = !in_array($this->oldStatus, [OrderStatus::Proforma, OrderStatus::Cancelled]);
        $isDeducting = !in_array($newStatus, [OrderStatus::Proforma, OrderStatus::Cancelled]);


        $hasStockAction = false;

        // --- الحالة 1: الطلب كان فعّالاً وما زال فعّالاً → نحسب الفروقات فقط ---
        if ($wasDeducting && $isDeducting) {
            // منتجات أُضيفت (موجودة في الجديد وغير موجودة في القديم)
            $added = $newItems->diffKeys($this->oldItems);
            // منتجات حُذفت (موجودة في القديم وغير موجودة في الجديد)
            $removed = $this->oldItems->diffKeys($newItems);
            // منتجات تغيّرت كميتها
            $changed = $newItems->filter(
                fn($item, $key) => $this->oldItems->has($key)
                && $this->oldItems->get($key)['qty'] != $item['qty']
            );

            // خصم المنتجات الجديدة
            foreach ($added as $item) {
                $this->processDeduction($inventoryService, $item, $currentBranch, $currentUser, $record->id, "إضافة صنف للطلب #{$record->number}");
                $hasStockAction = true;
            }

            // إرجاع المنتجات المحذوفة
            foreach ($removed as $item) {
                $inventoryService->addStockForBranch(
                    Product::find($item['product_id']),
                    $currentBranch,
                    (int) $item['qty'],
                    StockCase::Increase,
                    "حذف صنف من الطلب #{$record->number}",
                    $currentUser
                );
                $hasStockAction = true;
            }

            // معالجة الفروقات في الكميات
            foreach ($changed as $key => $newItem) {
                $oldQty = (float) $this->oldItems->get($key)['qty'];
                $diff = $newItem['qty'] - $oldQty;
                $product = Product::find($newItem['product_id']);

                if ($diff > 0) {
                    // زيادة الكمية → خصم إضافي
                    $this->processDeduction(
                        $inventoryService,
                        array_merge($newItem, ['qty' => $diff]),
                        $currentBranch,
                        $currentUser,
                        $record->id,
                        "زيادة كمية الطلب #{$record->number}"
                    );
                } else {
                    // تقليل الكمية → إرجاع الفرق
                    $inventoryService->addStockForBranch(
                        $product,
                        $currentBranch,
                        (int) abs($diff),
                        StockCase::Increase,
                        "تقليل كمية الطلب #{$record->number}",
                        $currentUser
                    );
                }
                $hasStockAction = true;
            }
        }
        // --- الحالة 2: الطلب تحوّل من غير فعّال إلى فعّال → خصم كل العناصر الجديدة ---
        elseif (!$wasDeducting && $isDeducting) {
            foreach ($newItems as $item) {
                $this->processDeduction(
                    $inventoryService,
                    $item,
                    $currentBranch,
                    $currentUser,
                    $record->id,
                    "تفعيل الطلب #{$record->number}"
                );
            }
            $hasStockAction = true;
        }
        // --- الحالة 3: الطلب تحوّل من فعّال إلى غير فعّال (إلغاء) → إرجاع كل العناصر القديمة ---
        elseif ($wasDeducting && !$isDeducting) {
            foreach ($this->oldItems as $item) {
                $inventoryService->addStockForBranch(
                    Product::find($item['product_id']),
                    $currentBranch,
                    (int) $item['qty'],
                    StockCase::Increase,
                    "إلغاء الطلب #{$record->number}",
                    $currentUser
                );
            }
            $hasStockAction = true;
        }

        if ($hasStockAction) {
            $inventoryService->updateAllBranches();
        }

        // سجل تحديث الطلب
        $record->orderLogs()->create([
            'log' => 'Invoice updated By: ' . $currentUser->name,
            'type' => 'updated',
        ]);
    }

    /**
     * معالجة الخصم مع التحقق من توفر المخزون أولاً
     */
    private function processDeduction($service, $itemData, $branch, $user, $orderId, $notes): void
    {
        $productId = (int) $itemData['product_id'];
        $qty = (int) $itemData['qty'];
        $product = Product::find($productId);

        /* if (!$service->isAvailableInBranch($product, $branch, $qty)) {
            $productName = $product?->name ?? "#$productId";
            Notification::make()->title("المخزون غير كافٍ: {$productName}")->danger()->send();
            throw new Halt();
        } */

        $service->deductStockForBranch($product, $branch, $qty, $notes, $user);
    }

    /**
     * This method runs AFTER the form is submitted but BEFORE the update happens.
     * It cleans up the data before it's saved.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. تحديد المستخدم الذي قام بالإجراء
        $data['caused_by'] = auth()->id();

        // 2. معالجة القيم المالية (تجنب الـ NULL)
        $data['paid'] = (float) ($data['paid'] ?? 0);
        $data['discount'] = (float) ($data['discount'] ?? 0);
        $data['shipping'] = (float) ($data['shipping'] ?? 0);
        $data['install'] = (float) ($data['install'] ?? 0);

        // 3. منطق العميل (Guest vs Registered)
        $data['customer_id'] = ($data['is_guest'] ?? false) ? null : $data['customer_id'];
        $data['guest_customer'] = ($data['is_guest'] ?? false) ? $data['guest_customer'] : null;

        return $data;
    }
}

