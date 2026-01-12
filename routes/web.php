<?php

use App\Enums\TruckType;
use App\Models\Truck;
use App\Models\TruckCargo;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/errors/{code}', function ($code): string {
    DB::transaction(function () use ($code) {
        if($code == 1){

            $trucks = Truck::out()->get();
            
            // جلب الرقم مع القفل (سيتم قفل السجل الأخير حتى تنتهي الحلقة بالكامل)
            $nextNumber = Truck::getNextTruckNumberValue(TruckType::Outer);
        }
        else if($code == 2){
            $trucks = TruckCargo::local()->get();
            $nextNumber = Truck::getNextTruckNumberValue(TruckType::Local);
        }else{
            return;
        }

        //dd($nextNumber);
        foreach ($trucks as $truck) {
            $prefix = 'TO';
            $yearMonth = date('Ym');
            if (! $truck->code) {
                $truck->code = $prefix.'-'.$yearMonth.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $truck->save();
            }
            $nextNumber++;
        }
    });

    return 'done';
});

Route::get('/clear/', function () {

    Artisan::call('optimize:clear');
    Artisan::call('filament:optimize-clear');

    return 'Cleared!';
});

Route::get('/artisan/{$command}', function ($command) {
    return Artisan::call($command);
});

Route::get('cargo/{truckCargo}', function (TruckCargo $truckCargo) {
    dd($truckCargo->attributesToArray());
});

Route::get('/test', function () {});
