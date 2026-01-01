<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_id',
        'type',
        'size',
        'product_id',
        'unit_quantity',
        'quantity',
        'real_quantity',
        'weight',
        'unit_price',
        'ton_weight',
        'note'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function truck()
    {
        return $this->belongsTo(\App\Models\Truck::class); //->converte();
    }
}
