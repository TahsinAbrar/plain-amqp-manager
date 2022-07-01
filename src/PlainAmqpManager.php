<?php

namespace App\Library\PlainAmqpManager;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Arr;

class PlainAmqpManager
{
    public $config;
    public $channel;
    public $connection;

    public function __construct()
    {
        $this->config = Arr::first(config('queue.connections.rabbitmq.hosts'));

        try {
            $this->getNewConnection();
        } catch (\Exception $exception) {
            // slient or log the error, so that if you are loading the file in warm config,
            // and that time there is a connectivity issue, it will boot the app, and
            // when the issue is resolved, it will be able to connect again.
        }
    }

    private function getNewConnection()
    {
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost']
        );

        $this->channel = $this->connection->channel();
    }

    /*
     * @param $payload
     * @param string $queue
     * @param bool $retry
    */
    public function pushRaw($payload, string $queue, bool $retry = true)
    {
        try {
            if ($this->connection === null || $this->connection->isConnected() === false) {
                $this->getNewConnection();
            }

            $this->channel->queue_declare($queue, false, true, false, false);

            [$message] = $this->createMessage($payload);

            $this->channel->basic_publish($message, '', $queue, true, false);
        } catch (\Exception $exception) {
            $this->connection->close();

            if ($retry === false) {
                throw $exception;
            }

            // app('log')->alert('Reconnecting RabbitMQ');

            $this->pushRaw($payload, $queue, false);
        }
    }

    private function createMessage($payload)
    {
        $properties = [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        $currentPayload = json_decode($payload, true);

        if ($correlationId = $currentPayload['id'] ?? null) {
            $properties['correlation_id'] = $correlationId;
        }

        $message = new AMQPMessage($payload, $properties);

        return [
            $message,
            $correlationId,
        ];
    }
}
