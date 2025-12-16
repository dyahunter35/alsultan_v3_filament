<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_id',
        'truck_id',
        'size',
        'quantity',
        'real_quantity',
        'weight',
        'unit_id',
        'unit_quantity',
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
