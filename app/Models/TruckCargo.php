<?php

namespace App\Models;

use App\Enums\CargoPriority;
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
        'base_total_foreign',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'priority' => CargoPriority::class,
        ];
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function truck()
    {
        return $this->belongsTo(\App\Models\Truck::class); // ->converte();
    }



    /* protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($value > 0)
                    return $value;

                $tonPrice = $attributes['ton_price'] ?? 0;
                $weight = $attributes['weight'] ?? 0;

                return $weight > 0 ? ($tonPrice * $weight / 1000000) : 0;
            },
            set: fn(mixed $value, array $attributes) => $value ?: (
                (($attributes['ton_price'] ?? 0) * ($attributes['weight'] ?? 0) / 1000000)
            ),
        );
    }

    protected function tonWeight(): Attribute
    {
        return Attribute::make(
            // استخدام $this يضمن الوصول للقيم المصبوبة والافتراضية بشكل أفضل
            get: function (mixed $value, array $attributes) {
                $weight = $attributes['weight'] ?? 0;
                $quantity = $attributes['unit_quantity'] ?? 0;
                return ($weight * $quantity) / 1000000;
            },
            // التخزين في قاعدة البيانات يفضل أن يكون محسوباً دائماً لضمان دقة التقارير
            set: fn(mixed $value, array $attributes) => ($attributes['weight'] ?? 0) * ($attributes['unit_quantity'] ?? 0) / 1000000,
        );
    } */

    /* protected function baseTotalForeign(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // الوصول للقيمة عبر $this->priority للتعامل مع الـ Enum بشكل صحيح
                if ($this->priority === CargoPriority::Weight) {
                    return ($attributes['ton_price'] ?? 0) * $this->ton_weight;
                }
                return ($attributes['unit_quantity'] ?? 0) * ($attributes['unit_price'] ?? 0);
            },
        );
    } */
    /* protected function tonPrice(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($value)
                    return $value;
                $weight = $attributes['weight'] ?? 0;
                return $weight > 0 ? ($attributes['unit_price'] / $weight * 1000000) : 0;
            },
            set: fn(mixed $value, array $attributes) => $value ?: (($attributes['unit_price'] / ($attributes['weight'] ?? 1) * 1000000)),
        );
    } */

}
