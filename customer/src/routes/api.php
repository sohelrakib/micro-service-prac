<?php

// routes/api.php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/customer/ping', function () {
    return [
        'service' => 'customer', 
        'list' => ['customer1', 'customer2', 'customer3'],
        'status' => 'ok'
    ];
});

Route::get('/customer/call-product', function () {
    $response = Http::get('http://product:8000/api/ping');
    return $response->json();
});

Route::get('/customer', function () {
    return ['service' => 'customer list', 'status' => 'ok'];
});
