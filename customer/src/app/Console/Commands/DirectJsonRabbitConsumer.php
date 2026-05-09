<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DirectJsonRabbitConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:direct-json-rabbit-consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume RabbitMQ messages from the direct exchange with JSON payload';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        // command: php artisan app:direct-json-rabbit-consumer

        // ------------------------------------------------------------
        // STEP 1: Create connection to RabbitMQ server
        // This opens a TCP connection between Laravel and RabbitMQ
        // ------------------------------------------------------------
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD')
        );

        // ------------------------------------------------------------
        // STEP 2: Create a channel
        // Channel is a virtual communication layer inside connection
        // All queue operations happen through channel
        // ------------------------------------------------------------
        $channel = $connection->channel();

        // ------------------------------------------------------------
        // STEP 3: Declare queue (safe even if already exists)
        // This ensures "user_queue" is available in RabbitMQ
        // ------------------------------------------------------------
        $channel->queue_declare(
            'user_queue',
            false,  // passive (check only)
            true,   // durable (survives RabbitMQ restart)
            false,  // exclusive (not limited to this connection)
            false   // auto delete
        );

        $this->info('Waiting for messages from user_queue...');

        // ------------------------------------------------------------
        // STEP 4: Callback function (CORE LOGIC)
        // This function is triggered automatically WHEN a message arrives
        //
        // IMPORTANT:
        // - You DO NOT call this manually
        // - RabbitMQ calls it when message is received
        // ------------------------------------------------------------
        $callback = function ($msg) {

            // --------------------------------------------------------
            // STEP 5: Read message body
            // RabbitMQ sends raw string, we decode JSON into array
            // --------------------------------------------------------
            $data = json_decode($msg->body, true);

            // --------------------------------------------------------
            // STEP 6: Logging received event for debugging/monitoring
            // --------------------------------------------------------
            \Log::info('JSON-EVENT-Message-Received:', $data);

            // --------------------------------------------------------
            // STEP 7: Business logic based on event type
            // This is where your application logic should happen
            // --------------------------------------------------------
            if (($data['event'] ?? null) === 'user.created') {

                echo "User created event received\n";

                // Example: call service layer instead of writing logic here
                // app(UserService::class)->handleUserCreated($data);
            }
        };

        // ------------------------------------------------------------
        // STEP 8: Start consuming messages
        // This tells RabbitMQ:
        // "Call $callback every time a message arrives in queue"
        // ------------------------------------------------------------
        $channel->basic_consume(
            'user_queue', // queue name
            '',            // consumer tag (auto-generated)
            false,         // no local
            true,          // no ack (auto acknowledge)
            false,         // exclusive
            false,         // no wait
            $callback      // callback function
        );

        // ------------------------------------------------------------
        // STEP 9: Keep worker alive
        // This loop keeps the command running forever
        // without this, consumer will exit immediately
        // ------------------------------------------------------------
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // note: while ($channel->is_consuming()) { ...} no need if we use cron job. but we should use Supervisor which is Best for RabbitMQ. 
        // command=php artisan rabbit:consume or php artisan app:direct-json-rabbit-consumer
        // autorestart=true
    }
}
