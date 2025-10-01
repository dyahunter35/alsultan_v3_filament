<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Enums\TruckType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Truck extends Model implements HasMedia
{
    use InteractsWithMedia;

    use HasFactory;

    protected $guarded = [

    ];

    protected $casts = [
        'truck_status' => TruckState::class,
        //'arrive_date' =>  Carbon::class,
        'type' => TruckType::class,
        'country'=>Country::class
    ];

    public function scopeConverte($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeLocal($query)
    {
        return $query->where('type', 2);
    }


    public function scopeFromBy(Builder $query, Model $from): Builder
    {
        return $query
            ->where('from_type', $from->getMorphClass())
            ->where('from_id', $from->getKey());
    }
    public function scopeOut($query)
    {
        return $query->where('type', 1);
    }

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function cargos()
    {
        return $this->hasMany(\App\Models\TruckCargo::class, "truck_id", 'id')->orderBy('id', 'DESC');
    }

     public function docs()
    {
        return $this->hasMany(\App\Models\TruckDocs::class, "truck_id", 'id')->orderBy('id', 'DESC');
    }

    public function trans()
    {
        return $this->hasMany(\App\Models\StoreTransaction::class, "truck_id", 'id')->orderBy('id', 'DESC');
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\TruckNote::class, "truck_id", 'id')->orderBy('id', 'DESC');
    }

    public function toBranch()
    {
        return $this->hasOne(\App\Models\Branch::class, 'id', "to");
    }

    public function companyId()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function contractorInfo()
    {
        return $this->belongsTo(\App\Models\Company::class,'contractor_id');
    }

    public function isConverted(): bool
    {
        return boolval($this->is_converted);
    }

    // add new method
    public function convertOuter()
    {
        $cargos = $this->cargos;

        foreach ($cargos as $cargo) {
            Product::create([
                'details_id' => $cargo->details_id,
                'store_id' => $this->record->to,
                'stored_by' => \Auth::user()->id,
                'quantity' => $cargo->quantity,
                'price' => '0',
                'note' => $cargo->note,
                'truck_id' => $this->record->id,
            ]);
        }

        $this->is_converted = true;
    }


}
