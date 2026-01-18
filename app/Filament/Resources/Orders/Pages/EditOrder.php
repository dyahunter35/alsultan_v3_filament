<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\StockCase;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Product;
use App\Services\InventoryService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
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
        // استخدام الـ Null Coalescing Operator هنا ممتاز لضمان الحسابات لاحقاً
        $data['paid'] = (float) ($data['paid'] ?? 0);
        $data['discount'] = (float) ($data['discount'] ?? 0);
        $data['shipping'] = (float) ($data['shipping'] ?? 0);
        $data['install'] = (float) ($data['install'] ?? 0);

        // 3. منطق العميل (Guest vs Registered)
        // نستخدم filter_var للتأكد من قيمة Boolean في حال كانت قادمة من Form
        /* $isGuest = filter_var($data['is_guest'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$isGuest) {
            // إذا كان عميل مسجل، نفرغ بيانات الضيف
            $data['guest_customer'] = null;
        } else {
            // إذا كان ضيف، نفرغ معرف العميل
            $data['customer_id'] = null;
        } */

        return $data;
    }

    /**
     * This is the main logic for handling the update process.
     * Overriding this gives us full control over the stock management transaction.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $inventoryService = new InventoryService;
        $currentBranch = Filament::getTenant();
        $currentUser = auth()->user();

        $newItemsData = collect($data['items'] ?? []);

        return DB::transaction(function () use ($record, $data, $inventoryService, $currentBranch, $currentUser) {

            // جلب المنتجات القديمة
            $originalItems = $record->items()->get()->keyBy('product_id');

            // تحديث الطلب بواسطة Filament
            $record->update($data);
            $record->refresh();

            // جلب المنتجات الجديدة من قاعدة البيانات
            $newItems = $record->items()->get()->keyBy('product_id');

            // معالجة فروقات الكميات
            foreach ($newItems as $productId => $newItem) {

                $oldQty = $originalItems[$productId]->qty ?? 0;
                $newQty = $newItem->qty;
                $newItem->sub_discount ??= 0;

                // لو الكمية ما اتغيرت → تجاهل
                if ($oldQty == $newQty) {
                    continue;
                }

                $product = Product::find($productId);

                // 1. رجع الكمية القديمة فقط لو كانت موجودة
                if ($oldQty > 0) {
                    $inventoryService->addStockForBranch(
                        $product,
                        $currentBranch,
                        $oldQty,
                        StockCase::Increase,
                        "Order Update #{$record->number}",
                        $currentUser
                    );
                }

                // 2. خصم الكمية الجديدة فقط
                if ($newQty > 0) {
                    $inventoryService->deductStockForBranch(
                        $product,
                        $currentBranch,
                        $newQty,

                        "Order Update #{$record->number}",
                        $currentUser
                    );
                }
            }

            // 3. المنتجات التي تم حذفها من الطلب فقط → رجّع كمياتها
            $deletedProducts = $originalItems->keys()->diff($newItems->keys());

            foreach ($deletedProducts as $deletedProductId) {
                $oldQty = $originalItems[$deletedProductId]->qty;
                $product = Product::find($deletedProductId);

                $inventoryService->addStockForBranch(
                    $product,
                    $currentBranch,
                    $oldQty,
                    StockCase::Increase,
                    "Order Item Removed #{$record->number}",
                    $currentUser
                );
            }

            $inventoryService->updateAllBranches();

            // سجل تحديث الطلب
            $record->orderLogs()->create([
                'log' => 'Invoice updated By: ' . $currentUser->name,
                'type' => 'updated',
            ]);

            return $record;
        });
    }
}
