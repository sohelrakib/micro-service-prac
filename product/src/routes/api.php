<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/product/ping', function () {
    return [
        'service' => 'product', 
        'list' => ['product1', 'product2', 'product3'],
        'status' => 'ok'
    ];
});


// Call customer service
Route::get('/product/call-customer', function () {
    $response = Http::get('http://customer:8000/api/customer/ping');
    return response()->json($response->json(), 200);
});

