<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/customers-log', [\App\Http\Controllers\CustomerController::class, 'index']);