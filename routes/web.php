<?php

use App\Models\TruckCargo;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/errors/{code}', function ($code) {
    abort($code);
});

Route::get('/clear/', function ($code) {
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
