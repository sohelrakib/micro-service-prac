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
    // url is: http://localhost:8002/api/customer/call-product
    try {
        $response = Http::throw()->get('http://product:8000/api/product/ping');
        echo "from customer service: <br>";
        echo "ping to product service: <br>";
        return $response->json();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
    
    // $response = Http::get('http://product:8000/api/product/ping');
    // return $response->json();
});

Route::get('/customer/call-product-by-network-alias', function () {
    // url is: http://localhost:8002/api/customer/call-product-by-network-alias
    try {
        $response = Http::throw()->get('http://prac-product-app:8000/api/product/ping');
        echo "from customer service using network alias: <br>";
        echo "ping to product service using network alias: <br>";
        return $response->json();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/customer', function () {
    return ['service' => 'customer list', 'status' => 'ok'];
});
