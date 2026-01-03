<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * This hook runs AFTER the order and its items have been successfully created.
     * This is the perfect place to handle stock deduction.
     */
    protected function afterCreate(): void
    {
        $inventoryService = new InventoryService;
        $currentBranch = Filament::getTenant();
        $currentUser = auth()->user();
        $order = $this->record;

        // تأكد أن هناك عناصر وإلا rollback
        if ($order->items->isEmpty()) {
            Notification::make()
                ->title(__('order.actions.create.notifications.at_least_one'))
                ->warning()
                ->send();
            throw new Halt(__('order.actions.create.errors.no_items'));
        }

        // جلب جميع المنتجات دفعة واحدة لتفادي N+1
        $productIds = $order->items->pluck('product_id')->unique()->values()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        DB::transaction(function () use ($inventoryService, $currentBranch, $currentUser, $order, $products) {
            foreach ($order->items as $item) {
                $product = $products->get($item->product_id);

                if (! $product) {
                    // محصول غير موجود — rollback
                    Notification::make()
                        ->title(__('order.actions.create.notifications.missing_product'))
                        ->body(__('order.actions.create.notifications.missing_product.message', ['id' => $item->product_id]))
                        ->danger()
                        ->send();

                    throw new Exception("Product #{$item->product_id} not found while creating order #{$order->id}");
                }

                // هنا نفترض أنه مسموح للوصول إلى رصيد سالب — ننفّذ الخصم مباشرةً
                $inventoryService->deductStockForBranch(
                    $product,
                    $currentBranch,
                    $item->qty,
                    "Order #{$order->number}",
                    $currentUser
                );
            }

            // ملاحظة: لا نستدعي updateAllBranches هنا (ثقيل). إذا أردت إعادة حساب كامل، شغّله كسكجولد أو background job.
            $order->orderLogs()->create([
                'log' => 'Invoice created By: '.($currentUser?->name ?? 'system'),
                'type' => 'created',
            ]);
        });
    }

    /**
     * This method runs BEFORE the main record is created.
     * It's used to prepare the data.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentBranch = Filament::getTenant();
        $currentUser = auth()->user();

        // Handle guest customer logic
        if (isset($data['is_guest'])) {
            if ($data['is_guest'] === false) {
                $data['guest_customer'] = null;
            } else {
                $data['customer_id'] = null;
            }
        }

        $data['number'] = Order::generateInvoiceNumber();
        $data['caused_by'] = $currentUser->id;
        $data['branch_id'] = $currentBranch->id;

        return $data;
    }
}
