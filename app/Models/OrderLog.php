<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $invoice_id
 * @property json $log
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 * @property Invoice $invoice
 */
class OrderLog extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'order_id',
        'log',
        'type',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'log' => 'json',
    ];

    /**
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
