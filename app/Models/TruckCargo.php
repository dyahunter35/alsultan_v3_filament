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
            // جلب القيمة: إذا كانت القيمة فارغة في القاعدة، قم بحسابها برمجياً
            get: fn (mixed $value, array $attributes) => $value ?: ($attributes['unit_price'] * $attributes['unit_quantity']),

            // تخزين القيمة: التأكد من تخزين القيمة المحسوبة إذا لم يتم إدخال قيمة يدوية
            set: fn (mixed $value, array $attributes) => [
                'ton_price' => $value ?: ($attributes['unit_price'] * $attributes['unit_quantity']),
            ],
        );
    }

    protected function tonWeight(): Attribute
    {
        return Attribute::make(
            // جلب القيمة: إذا كانت القيمة فارغة في القاعدة، قم بحسابها برمجياً
            get: fn (mixed $value, array $attributes) => $value ?: (($attributes['weight'] * $attributes['unit_quantity']) / 1000000),

            // تخزين القيمة: التأكد من تخزين القيمة المحسوبة إذا لم يتم إدخال قيمة يدوية
            set: fn (mixed $value, array $attributes) => [
                'ton_weight' => $value ?: (($attributes['weight'] * $attributes['unit_quantity']) / 1000000),
            ],
        );
    }
}
