<?php

return [
    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
    'port' => (int) env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'exchange' => env('RABBITMQ_EXCHANGE', 'outbox.events'),
    'exchange_type' => env('RABBITMQ_EXCHANGE_TYPE', 'fanout'),
    'exchange_durable' => (bool) env('RABBITMQ_EXCHANGE_DURABLE', true),
    'routing_key' => env('RABBITMQ_ROUTING_KEY', ''),
    'queue' => env('RABBITMQ_QUEUE', ''),
    'queue_durable' => (bool) env('RABBITMQ_QUEUE_DURABLE', true),
    'queue_exclusive' => (bool) env('RABBITMQ_QUEUE_EXCLUSIVE', false),
    'queue_auto_delete' => (bool) env('RABBITMQ_QUEUE_AUTO_DELETE', false),
    'binding_key' => env('RABBITMQ_BINDING_KEY', ''),
    'publish_retries' => (int) env('RABBITMQ_PUBLISH_RETRIES', 3),
    'publish_backoff_ms' => (int) env('RABBITMQ_PUBLISH_BACKOFF_MS', 250),
    'connect_timeout' => (int) env('RABBITMQ_CONNECT_TIMEOUT', 3),
    'read_write_timeout' => (int) env('RABBITMQ_READ_WRITE_TIMEOUT', 3),
];
