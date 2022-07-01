# PlainAmqpManager

RabbitMQ driver for Laravel Queue. Supports Laravel Octane

## Purpose

For using RabbitMQ as a queue in Laravel, `vyuldashev/laravel-queue-rabbitmq` is widely used package. But with Laravel Octane, this package has an issue with reconnect process while the channel is closed automatically after a certain period of time.

Here, one thing you need to know:
> if a channel connection is not being used for a certain period of time, then the connection is being closed from RabbitMQ end automatically.

I have created an [issue](https://github.com/vyuldashev/laravel-queue-rabbitmq/issues/460) regarding this in the `vyuldashev/laravel-queue-rabbitmq` package, but this issue has not yet been resolved.

For solving the issue at my end quickly, I have created a very simple AMQP connection for pushing raw json data in queue along with reconnection functionality if the channel is being closed.

If anyone is facing the same issue and don't have the time to debug and provide a hotfix at `vyuldashev/laravel-queue-rabbitmq` package, then you can use this code.

But I suggest to provide a hotfix to `vyuldashev/laravel-queue-rabbitmq` package if you have some time. With this, we can utilize the existing package functionality. I am also planning to do the same, that's why I didn't prepare this as package.

## Installation

As I did not create a package structure till now, so to use this code, I suggest to follow this approach:

- create a folder under `app/` directory `app/Library/PlainAmqpManager/`
- copy the file from `src/` folder and put `PlainAmqpManager.php` and `PlainAmqpManagerServiceProvider.php` file into `app/Library/PlainAmqpManager/` directory.
- Add this below line in `config/app.php` file for registering custom service provider. `App\Library\PlainAmqpManager\PlainAmqpManagerServiceProvider::class`
- Also, add the below line within `config/octane.php` file's `warm` array to load on boot, so that only single connection will need for all requests:
`\App\Library\PlainAmqpManager\PlainAmqpManager::class`
- Also, please ensure that if you have the rabbitmq config on your `config/queue.php` file as below format:

```php

    'connections' => [
        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'connection' => \PhpAmqpLib\Connection\AMQPLazyConnection::class,

            'hosts' => [
                [
                    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                    'port' => env('RABBITMQ_PORT', 5672),
                    'user' => env('RABBITMQ_USER', 'guest'),
                    'password' => env('RABBITMQ_PASSWORD', 'guest'),
                    'vhost' => env('RABBITMQ_VHOST', ''),
                ],
            ],

            'options' => [],
        ],
    ]
```

## Final thoughts

It would be better if I would create a package structure, but as I was planning to contribute on `vyuldashev/laravel-queue-rabbitmq` package, that's why, keeping this in a very simple way. Though this is not the good approach, but easiest approach. If you need to use separate functionality, you can change the `PlainAmqpManager` class.
