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


    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($value)
                    return $value;
                $weight = $attributes['weight'] ?? 0;
                return $weight > 0 ? ($attributes['ton_price'] * $weight / 1000000) : 0;
            },
            set: fn(mixed $value, array $attributes) => $value ?: (($attributes['ton_price'] * ($attributes['weight'] ?? 1) / 1000000)),
        );
    }

    protected function tonPrice(): Attribute
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
    }

    protected function tonWeight(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => $value ?: (($attributes['weight'] * ($attributes['unit_quantity'] ?? 0)) / 1000000),

            set: fn(mixed $value, array $attributes) => $value ?: (($attributes['weight'] * ($attributes['unit_quantity'] ?? 0)) / 1000000),
        );
    }
    //$base_total_foreign = ($item->priority == CargoPriority::Weight) ? $item->ton_price * $weight_ton : $item->quantity * $item->unit_price; // $weight_ton * $item->unit_price;
    protected function baseTotalForeign(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => $value ?: ($attributes['priority'] == CargoPriority::Weight ? $attributes['ton_price'] * $attributes['ton_weight'] : $attributes['quantity'] * $attributes['unit_price']),
            //set: fn(mixed $value, array $attributes) => $value ?: ($attributes['priority'] == CargoPriority::Weight ? $attributes['ton_price'] * $attributes['ton_weight'] : $attributes['quantity'] * $attributes['unit_price']),
        );
    }

}
