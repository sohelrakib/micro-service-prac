<?php

use Illuminate\Support\Facades\Route;
use App\Jobs\ProcessUserJob;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/rmq-send-direct', function () {
    // php artisan queue:work rabbitmq --queue=direct_queue

    ProcessUserJob::dispatch([
        'name' => 'Sohel',
        'type' => 'direct-test'
    ]);

    \Log::info("Message sent:", ['name' => 'Sohel', 'type' => 'direct-test']);
    return "Message sent to RMQ";
});