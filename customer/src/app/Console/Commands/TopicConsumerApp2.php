<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class TopicConsumerApp2 extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan make:command TopicConsumerApp2 
     * php artisan rabbitmq:topic-consumer-app2
     *
     * @var string
     */
    protected $signature = 'rabbitmq:topic-consumer-app2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from the topic exchange in app 2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var RabbitMQQueue $queue */
        // Get RabbitMQ connection from Laravel queue system
        // using package: vladimir-yuldashev/laravel-queue-rabbitmq
        $queue = Queue::connection();
        $connection = $queue->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(
            'app_topic_exchange', // exchange name
            'topic',              // exchange type
            false,                // passive
            true,                 // durable
            false                 // auto_delete
        );


        // --------------------------------------------------------
        // STEP 1: Declare queue for this service
        // --------------------------------------------------------
        $channel->queue_declare(
            'product_topic_service_queue2', // queue name
                                // messages are stored here

            false,                // passive
                                // false = create if not exists

            true,                 // durable
                                // queue survives RabbitMQ restart

            false,                // exclusive
                                // false = multiple connections allowed

            false                 // auto_delete
                                // false = queue won't be deleted automatically
        );


        // --------------------------------------------------------
        // STEP 2: Bind ALL events using "#"
        // --------------------------------------------------------
        $channel->queue_bind(

            'product_topic_service_queue2',  // queue name
                                // destination queue

            'app_topic_exchange',  // exchange name
                                // source router

            '#'                  // binding key
                         // means:
                         // ALL events
                         // user.created
                         // order.failed
                         // payment.success
        );


        $this->info('App 2 waiting for topic messages...');


        // --------------------------------------------------------
        // STEP 3: Register consumer callback
        // --------------------------------------------------------
        $channel->basic_consume(

            'product_topic_service_queue2', // queue name

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
        $this->info('App 2 received message');
        Log::info('JSON-EVENT-Topic-Message-Received:', $data);
        $this->info('Data: ' . json_encode($data));

        // --------------------------------------------------------
        // STEP 10: Acknowledge message
        //
        // RabbitMQ removes message from queue after ACK
        // --------------------------------------------------------
        $message->ack();
    }
}
