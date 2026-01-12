<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'ton_price',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function truck()
    {
        return $this->belongsTo(\App\Models\Truck::class); // ->converte();
    }

    protected function tonPrice(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $value ?: ($attributes['unit_price'] / ($attributes['weight'] ?? 0)*1000000),

            // في الـ set، نرجع القيمة مباشرة، وLaravel سيهتم بإسنادها لـ ton_price
            set: fn (mixed $value, array $attributes) => $value ?: ($attributes['unit_price'] / ($attributes['weight'] ?? 0)*1000000),
        );
    }

    protected function tonWeight(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $value ?: (($attributes['weight'] * ($attributes['unit_quantity'] ?? 0)) / 1000000),

            set: fn (mixed $value, array $attributes) => $value ?: (($attributes['weight'] * ($attributes['unit_quantity'] ?? 0)) / 1000000),
        );
    }
}
