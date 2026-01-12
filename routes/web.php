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

Route::get('test', function ($command) {
    $data = ['expense_type_id' => 17,
        'payer_id' => 2,
        'payer_type' => "App\Models\User",
        'truck_id' => 23,
        'amount' => 6000000.0,
        'notes' => null,

        'payment_reference' => 65656.0,
        'is_paid' => 1,
        'created_at' => '2026-01-12 09:41:14',
    ];
});
