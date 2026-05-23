<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Message\AMQPMessage;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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

    public function fanoutJsonEvent() 
    {
        // ------------------------------------------------------------
        // STEP 1: Connect to RabbitMQ server
        // ------------------------------------------------------------
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),      // RabbitMQ host/container name
            env('RABBITMQ_PORT'),      // RabbitMQ port
            env('RABBITMQ_USER'),      // RabbitMQ username
            env('RABBITMQ_PASSWORD')   // RabbitMQ password
        );


        // ------------------------------------------------------------
        // STEP 2: Create communication channel
        // ------------------------------------------------------------
        $channel = $connection->channel();


        // ------------------------------------------------------------
        // STEP 3: Declare FANOUT exchange
        //
        // FANOUT exchange:
        // - broadcasts message to ALL bound queues
        // - routing key is ignored
        // ------------------------------------------------------------
        $channel->exchange_declare(
            'logs_fanout',  // exchange name
            'fanout',       // exchange type
            false,          // passive:
                            // false = create exchange if not exists
            true,           // durable:
                            // true = survive RabbitMQ restart
            false           // auto delete:
                            // false = do not delete automatically
        );

        
        
        // ------------------------------------------------------------
        // STEP 4: Prepare JSON payload
        // ------------------------------------------------------------
        $data = [
            'event' => 'user.created',
            'user_id' => 1,
            'name' => 'Sohel Rakib',
            'time' => now()->toDateTimeString(),
        ];


        // ------------------------------------------------------------
        // STEP 5: Convert array → JSON message
        // ------------------------------------------------------------
        $message = new AMQPMessage(
            json_encode($data), // message body
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                // persistent message
                // survives RabbitMQ restart
            ]
        );


        // ------------------------------------------------------------
        // STEP 6: Publish message to FANOUT exchange
        //
        // IMPORTANT:
        // routing key is ignored in fanout exchange
        // ------------------------------------------------------------
        $channel->basic_publish(
            $message,       // message object
            'logs_fanout',  // exchange name
            ''              // routing key (ignored in fanout)
        );


        // ------------------------------------------------------------
        // STEP 7: Close channel and connection
        // ------------------------------------------------------------
        $channel->close();
        $connection->close();

        Log::info('FANOUT-JSON-EVENT-Message-Sent:', $data);

        return response()->json([
            'status' => 'Fanout message published successfully'
        ]);
    }

    public function directJsonEventWithExchange()
    {
        // ------------------------------------------------------------
        // STEP 1: Get RabbitMQ connection from Laravel queue system
        // ------------------------------------------------------------
        /** @var RabbitMQQueue $queue */
        $queue = Queue::connection();


        // ------------------------------------------------------------
        // STEP 2: Get underlying RabbitMQ connection
        // ------------------------------------------------------------
        $connection = $queue->getConnection();

        
        // ------------------------------------------------------------
        // STEP 3: Create channel
        // ------------------------------------------------------------
        $channel = $connection->channel();

        
        // ------------------------------------------------------------
        // STEP 4: Declare DIRECT exchange
        //
        // DIRECT exchange routes messages
        // based on exact routing key matching
        // ------------------------------------------------------------
        $channel->exchange_declare(
            'direct_app_exchange', // exchange name

            'direct',       // exchange type

            false,          // passive
                            // false = create if not exists

            true,           // durable
                            // survives RabbitMQ restart

            false           // auto delete
        );


        // ------------------------------------------------------------
        // STEP 5: Prepare JSON payload
        // ------------------------------------------------------------
        $data = [
            'event' => 'product.created',
            'product_id' => 1,
            'name' => 'MacBook Pro M5',
            'email' => 'macbook@example.com',
            'created_at' => now()->toDateTimeString(),
        ];


        // ------------------------------------------------------------
        // STEP 6: Create RabbitMQ message
        // ------------------------------------------------------------
        $msg = new AMQPMessage(
            json_encode($data),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );


        // ------------------------------------------------------------
        // STEP 7: Publish message to DIRECT exchange
        //
        // routing key = product.created
        // ------------------------------------------------------------
        $channel->basic_publish(
            $msg,               // message
            'direct_app_exchange',     // exchange name
            'product.created'      // routing key
        );


        return response()->json([
            'status' => 'Direct message published successfully',
            'data' => $data
        ]);
    }
}