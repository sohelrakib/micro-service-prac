<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class DirectExchangeConsumerApp2 extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan make:command DirectExchangeConsumerApp2
     * php artisan rabbitmq:direct-exchange-consumer-app2
     *
     * @var string
     */
    protected $signature = 'rabbitmq:direct-exchange-consumer-app2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from the direct exchange for App2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // --------------------------------------------------------
        // STEP 1: Connect to RabbitMQ
        // --------------------------------------------------------
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD')
        );


        // --------------------------------------------------------
        // STEP 2: Create channel
        // --------------------------------------------------------
        $channel = $connection->channel();

        // --------------------------------------------------------
        // STEP 3: Declare DIRECT exchange
        // --------------------------------------------------------
        $channel->exchange_declare(
            'direct_app_exchange',
            'direct',
            false,
            true,
            false
        );


        // --------------------------------------------------------
        // STEP 4: Declare App2 queue
        // --------------------------------------------------------
        $channel->queue_declare(
            'product_direct_queue_app2',
            false,
            true,
            false,
            false
        );


        // --------------------------------------------------------
        // STEP 5: Bind queue to exchange
        //
        // queue listens for:
        // routing_key = product.created
        // --------------------------------------------------------
        $channel->queue_bind(
            'product_direct_queue_app2', // queue

            'direct_app_exchange',    // exchange

            'product.created'     // binding key
        );


        $this->info('App2 waiting for messages...');



        // --------------------------------------------------------
        // STEP 6: Register consumer
        //
        // This tells RabbitMQ:
        //
        // "Whenever a message arrives in this queue,
        // call handleMessage() automatically."
        // --------------------------------------------------------
        $channel->basic_consume(

            'product_direct_queue_app2', // queue name
                            // Consumer listens to this queue

            '',                // consumer tag
                            // Unique consumer identifier
                            // Empty string = RabbitMQ auto-generates one

            false,             // no_local
                            // true  = do NOT receive messages
                            //         published from same connection
                            //
                            // false = receive all messages
                            //
                            // RabbitMQ usually ignores this parameter

            false,             // no_ack
                            // false = MANUAL acknowledgement
                            //
                            // You must call:
                            // $message->ack()
                            //
                            // after successful processing
                            //
                            // Recommended for reliability
                            //
                            // true = AUTO acknowledge
                            // Message removed immediately after delivery
                            // Risk: message lost if app crashes

            false,             // exclusive
                            // true  = only this consumer can use queue
                            //
                            // false = multiple consumers allowed
                            //
                            // Usually false in microservices

            false,             // no_wait
                            // false = wait for RabbitMQ response
                            //
                            // true = do not wait for server confirmation
                            //
                            // Usually false

            [$this, 'handleMessage']
                            // callback function
                            //
                            // RabbitMQ automatically calls:
                            //
                            // $this->handleMessage($message)
                            //
                            // whenever a new message arrives
        );


        // --------------------------------------------------------
        // STEP 7: Keep worker alive
        // --------------------------------------------------------
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * Handle incoming message
     */
    public function handleMessage(AMQPMessage $message): void
    {
        // decode JSON
        $data = json_decode($message->getBody(), true);
        Log::info('JSON-EVENT-Direct-Message-Received:', $data);

        $this->info('App2 received message');

        print_r($data);

        // acknowledge message
        $message->ack();
    }
}
