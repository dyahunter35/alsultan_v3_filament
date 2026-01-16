<?php

namespace App\Models;

use App\Enums\StockCase;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'type',
        'quantity_change',
        'new_quantity',
        'truck_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type' => StockCase::class,
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // 🟢 When a stock history record is created
        static::created(function (StockHistory $history) {
            $services = new InventoryService;
            $services->updateAllBranches();

            $product = $history->product;

            if (($product->total_stock >= $product->security_stock) && $product->low_stock_notified_at) {
                $product->update(['low_stock_notified_at' => null]);
            }
        });

        // 🟡 When a stock history record is updated
        static::updated(function (StockHistory $history) {
            $services = new InventoryService;
            $services->updateStockInBranch($history->product, $history->branch);

            $product = $history->product;

            if (($product->total_stock >= $product->security_stock) && $product->low_stock_notified_at) {
                $product->update(['low_stock_notified_at' => null]);
            }
        });

        // 🔴 When a stock history record is deleted
        static::deleted(function (StockHistory $history) {
            $services = new InventoryService;
            $services->updateStockInBranch($history->product, $history->branch);
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
