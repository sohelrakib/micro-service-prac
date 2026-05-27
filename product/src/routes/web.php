<?php

use App\Http\Controllers\RabbitmqController;
use Illuminate\Support\Facades\Route;
use App\Jobs\ProcessUserJob;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/rmq-send-direct', function () {
    // php artisan queue:work rabbitmq --queue=direct_queue

    //php artisan vendor:publish --provider="VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider"

    ProcessUserJob::dispatch([
        'name' => 'Sohel',
        'type' => 'direct-test'
    ]);

    Log::info("Message sent:", ['name' => 'Sohel', 'type' => 'direct-test']);
    return "Message sent to RMQ";
});

Route::get('/rmq-send-direct-json-event', [RabbitmqController::class, 'directJsonEvent']);
Route::get('/rmq-send-fanout-json-event', [RabbitmqController::class, 'fanoutJsonEvent']);
Route::get('/rmq-send-direct-json-event_with_exchange', [RabbitmqController::class, 'directJsonEventWithExchange']);
Route::get('/rmq-send-topic-json-event', [RabbitmqController::class, 'topicJsonEvent']);