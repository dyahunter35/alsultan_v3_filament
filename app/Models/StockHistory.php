<?php

namespace App\Models;

use App\Enums\StockCase;
use App\Models\Pivots\BranchProduct;
use App\Services\InventoryService;
use Filament\Facades\Filament;
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
        'type' => StockCase::class
    ];
    /**
     * The "booted" method of the model.
     */

    protected static function booted(): void
    {
        // This event fires AFTER a new StockHistory record is created.
        static::created(function (StockHistory $history) {
            $servies = new InventoryService;

            $servies->updateAllBranches();

            $product = $history->product;

            if (($product->total_stock >= $product->security_stock) && $product->low_stock_notified_at)
                $product->update(['low_stock_notified_at' => null]);
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
}
