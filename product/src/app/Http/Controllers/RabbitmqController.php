<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Message\AMQPMessage;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class RabbitmqController extends Controller
{
    public function directJsonEvent()
    {
        /** @var RabbitMQQueue $queue */
        $queue = Queue::connection();

        $connection = $queue->getConnection();
        $channel = $connection->channel();

        $channel->queue_declare(
            'user_queue', // Queue name
            false,        // Create queue if not exists
            true,         // Keep queue after RabbitMQ restart
            false,        // Allow multiple connections to use it
            false         // Do not auto delete the queue
        );

        $data = [
            'event' => 'user.created',
            'user_id' => 1,
            'name' => 'users-name2',
            'email' => 'users-email@example.com',
            'dob' => '1990-01-01',
            'created_at' => now()->toDateTimeString(),
        ];

        $msg = new AMQPMessage(json_encode($data));

        $channel->basic_publish(
            $msg,         // Message object/content
            '',           // Exchange name
            'user_queue'  // Routing key / queue name
        );

        Log::info('JSON-EVENT-Message-Sent:', $data);

        return response()->json([
            'status' => 'Message published',
        ]);
    }
}