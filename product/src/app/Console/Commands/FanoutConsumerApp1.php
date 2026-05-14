<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FanoutConsumerApp1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:fanout-consumer-app1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from the fanout exchange for App1';

    /**
     * Execute the console command.
     * php artisan rabbitmq:fanout-consumer-app1
     */
    public function handle()
    {
        // --------------------------------------------------------
        // STEP 1: Connect to RabbitMQ
        // --------------------------------------------------------
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),      // RabbitMQ host
            env('RABBITMQ_PORT'),      // RabbitMQ port
            env('RABBITMQ_USER'),      // RabbitMQ username
            env('RABBITMQ_PASSWORD')   // RabbitMQ password
        );

        // --------------------------------------------------------
        // STEP 2: Create channel
        // --------------------------------------------------------
        $channel = $connection->channel();

        // --------------------------------------------------------
        // STEP 3: Declare FANOUT exchange
        // Must exist on both producer and consumer side
        // --------------------------------------------------------
        $channel->exchange_declare(
            'logs_fanout', // exchange name
            'fanout',      // exchange type
            false,         // passive
            true,          // durable
            false          // auto delete
        );

        // --------------------------------------------------------
        // STEP 4: Declare queue for App 1
        // --------------------------------------------------------
        $channel->queue_declare(
            'fanout_queue_1',  // queue name
            false,         // passive
            true,          // durable
            false,         // exclusive
            false          // auto delete
        );

        // --------------------------------------------------------
        // STEP 5: Bind queue to exchange
        //
        // Means:
        // fanout_queue_1 listens to logs_fanout exchange
        // --------------------------------------------------------
        $channel->queue_bind(

            'fanout_queue_1',  // queue name

            'logs_fanout'  // exchange name
        );

        $this->info('App 1 waiting for fanout messages...');

        // --------------------------------------------------------
        // STEP 6: Register consumer callback
        // --------------------------------------------------------
        $channel->basic_consume(

            'fanout_queue_1', // queue name

            '',           // consumer tag
                          // empty = auto generated

            false,        // no local
                          // not used by RabbitMQ

            false,        // no ack
                          // false = manual acknowledgement

            false,        // exclusive
                          // false = multiple consumers allowed

            false,        // no wait
                          // false = wait for server response

            [$this, 'handleMessage']
        );

        // --------------------------------------------------------
        // STEP 7: Keep consumer alive forever
        // --------------------------------------------------------
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * Handle incoming messages
     */
    public function handleMessage(AMQPMessage $message): void
    {
                // --------------------------------------------------------
        // STEP 8: Decode JSON payload
        // --------------------------------------------------------
        $data = json_decode($message->getBody(), true);

        // --------------------------------------------------------
        // STEP 9: Show message
        // --------------------------------------------------------
        $this->info('App 1 received message');
        \Log::info('JSON-EVENT-Message-Received:', $data);
        $this->info('Data: ' . json_encode($data));

        // --------------------------------------------------------
        // STEP 10: Acknowledge message
        //
        // RabbitMQ removes message from queue after ACK
        // --------------------------------------------------------
        $message->ack();
    }
    
}
