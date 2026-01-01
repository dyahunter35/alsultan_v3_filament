<?php

namespace App\Services;

use App\Enums\StockCase;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Scopes\IsVisibleScope;
use App\Models\StockHistory;
use App\Models\Truck;
use App\Models\User;
use Exception;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

/**
 * Class InventoryService
 *
 * Handles all inventory logic based on a multi-tenant (Branch) system.
 * The source of truth for stock levels is the `branch_product` pivot table.
 * All changes are logged in the `stock_histories` table.
 */
class InventoryService
{
    /**
     * إضافة كمية إلى مخزون منتج في فرع معين.
     *
     * ملاحظة: هذه الدالة ستُنشئ صف pivot إن لم يكن موجوداً وتقوم بقفل الصف أثناء التعديل.
     */
    public function addStockForBranch(Product $product, Branch $branch, int $quantity, StockCase $type = StockCase::Initial, ?string $notes = 'Manual Update', ?User $causer = null, ?Truck $truck = null): StockHistory
    {
        return DB::transaction(function () use ($product, $branch, $quantity, $type, $notes, $causer, $truck) {
            // Lock the pivot row (or create it if missing)
            $pivotRow = DB::table('branch_product')
                ->where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->lockForUpdate()
                ->first();

            if (! $pivotRow) {
                // Create pivot row with zero quantity so subsequent logic is consistent
                DB::table('branch_product')->insert([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'total_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // re-fetch locked row
                $pivotRow = DB::table('branch_product')
                    ->where('product_id', $product->id)
                    ->where('branch_id', $branch->id)
                    ->lockForUpdate()
                    ->first();
            }

            $currentQty = $pivotRow->total_quantity ?? 0;
            $newQty = $currentQty + $quantity;

            // إنشاء سجل التاريخ
            $history = StockHistory::withoutGlobalScopes()->create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'type' => $type->value ?? 'increase',
                'quantity_change' => $quantity,
                'new_quantity' => $newQty,
                'truck_id' => $truck?->id ?? null,
                'notes' => $notes,
                'user_id' => $causer?->id,
            ]);

            // تحديث الـ pivot مباشرة لضمان التناسق الفوري
            DB::table('branch_product')
                ->where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->update(['total_quantity' => $newQty, 'updated_at' => now()]);

            return $history;
        });
    }

    /**
     * خصم كمية من مخزون منتج في فرع معين بطريقة آمنة.
     *
     * ملاحظة: يسمح بخروج رصيد سالب (حسب رغبتك) — لا يقوم بالـ throw عند الوصول لسالب.
     */
    public function deductStockForBranch(Product $product, Branch $branch, int $quantity, ?string $notes = 'Sale',  ?User $causer = null, ?Truck $truck = null): StockHistory
    {
        return DB::transaction(function () use ($product, $branch, $quantity, $notes, $causer, $truck) {
            // Lock or create pivot row
            $pivotRow = DB::table('branch_product')
                ->where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->lockForUpdate()
                ->first();

            if (! $pivotRow) {
                // إذا لم يوجد صف سابقاً، أنشئ واحداً بكمية صفر
                DB::table('branch_product')->insert([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'total_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $pivotRow = DB::table('branch_product')
                    ->where('product_id', $product->id)
                    ->where('branch_id', $branch->id)
                    ->lockForUpdate()
                    ->first();
            }

            $currentQty = $pivotRow->total_quantity ?? 0;

            // لا نرمي استثناء عند الوصول للقيمة السالبة — المستخدم طلب السماح بالسالب.
            $newQty = $currentQty - $quantity;

            $history = StockHistory::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'type' => 'decrease',
                'quantity_change' => $quantity,
                'new_quantity' => $newQty,
                'truck_id' => $truck?->id ?? null,
                'notes' => $notes,
                'user_id' => $causer?->id,
            ]);

            // تحديث pivot
            DB::table('branch_product')
                ->where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->update(['total_quantity' => $newQty, 'updated_at' => now()]);

            return $history;
        });
    }

    /**
     * التحقق مما إذا كانت كمية معينة من منتج متوفرة في فرع معين.
     * يبقى مفيداً لواجهات العرض أو لتحذيرات قبل الحفظ.
     */
    public function isAvailableInBranch(Product $product, Branch $branch, int $quantity): bool
    {
        $pivot = $product->branches()->find($branch->id)?->pivot;
        $currentQty = $pivot->total_quantity ?? 0;

        return $currentQty >= $quantity;
    }

    /**
     * Recalculates and updates the total stock for a specific product in a specific branch.
     */
    public function updateStockInBranch(Product $product, Branch $branch): int
    {
        $total = StockHistory::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->sum(DB::raw('CASE WHEN type = "increase" or type = "initial" THEN quantity_change ELSE -quantity_change END'));

        return DB::table('branch_product')
            ->where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->update(['total_quantity' => $total, 'updated_at' => now()]);
    }

    /**
     * This will update all pivot rows; kept for admin/maintenance use.
     * **لا تقم باستدعائها داخل أي request عادي.** استخدمها كـ command أو queued job.
     */
    public function updateAllBranches()
    {
        DB::table('branch_product')->update([
            'total_quantity' => DB::raw(
                '(COALESCE((SELECT SUM(CASE WHEN type = "increase" or type = "initial" THEN quantity_change ELSE -quantity_change END)
                  FROM stock_histories
                  WHERE stock_histories.product_id = branch_product.product_id
                  AND stock_histories.branch_id = branch_product.branch_id), 0))'
            ),
            'updated_at' => now()
        ]);
    }

    /**
     * تحويل مخزون من فرع إلى فرع آخر
     */
    public function transferStock(Product $product, Branch $fromBranch, Branch $toBranch, float $quantity, ?string $notes = null, ?User $causer = null, ?Truck $truck = null)
    {
        return DB::transaction(function () use ($product, $fromBranch, $toBranch, $quantity, $notes, $causer, $truck) {

            // 1. خصم من المخزن المصدر
            $this->deductStockForBranch(
                product: $product,
                branch: $fromBranch,
                quantity: $quantity,
                notes: "تحويل صادر: " . ($notes ?? "إلى " . $toBranch->name),
                causer: $causer,
                truck: $truck
            );

            // 2. إضافة إلى المخزن الهدف
            $this->addStockForBranch(
                product: $product,
                branch: $toBranch,
                quantity: $quantity,
                type: StockCase::Increase,
                notes: "تحويل وارد: " . ($notes ?? "من " . $fromBranch->name),
                causer: $causer,
                truck: $truck
            );
        });
    }
}
