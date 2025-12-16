<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/errors/{code}', function ($code) {
    abort($code);
});

Route::get('/clear/', function ($code) {
    Artisan::call('optimize:clear');
    Artisan::call('filament:optimize-clear');
    return "Cleared!";
});
