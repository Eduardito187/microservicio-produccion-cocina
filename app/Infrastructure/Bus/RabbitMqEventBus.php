<?php

namespace App\Infrastructure\Bus;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Application\Shared\BusInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;
use DateTimeImmutable;

class RabbitMqEventBus implements BusInterface
{
    public function publish(string $eventId, string $name, array $payload, DateTimeImmutable $occurredOn): void
    {
        $messageBody = json_encode([
            'event_id' => $eventId,
            'event' => $name,
            'occurred_on' => $occurredOn->format(DATE_ATOM),
            'payload' => $payload,
        ], JSON_UNESCAPED_SLASHES);

        if (!is_string($messageBody)) {
            throw new \RuntimeException('Unable to encode outbox message');
        }

        $routingKey = $this->resolveRoutingKey($name);
        $retries = (int) config('rabbitmq.publish_retries', 3);
        $backoffMs = (int) config('rabbitmq.publish_backoff_ms', 250);

        $attempt = 0;
        while (true) {
            $attempt++;
            $connection = null;
            $channel = null;

            try {
                $connection = new AMQPStreamConnection(
                    config('rabbitmq.host'),
                    (int) config('rabbitmq.port'),
                    config('rabbitmq.user'),
                    config('rabbitmq.password'),
                    config('rabbitmq.vhost'),
                    false,
                    'AMQPLAIN',
                    null,
                    'en_US',
                    (int) config('rabbitmq.connect_timeout'),
                    (int) config('rabbitmq.read_write_timeout')
                );

                $channel = $connection->channel();

                $exchange = config('rabbitmq.exchange');
                $exchangeType = config('rabbitmq.exchange_type', 'fanout');
                $durable = (bool) config('rabbitmq.exchange_durable', true);

                if (is_string($exchange) && $exchange !== '') {
                    $channel->exchange_declare(
                        $exchange,
                        $exchangeType,
                        false,
                        $durable,
                        false
                    );
                }

                $queue = config('rabbitmq.queue');
                if (is_string($queue) && $queue !== '') {
                    $channel->queue_declare(
                        $queue,
                        false,
                        (bool) config('rabbitmq.queue_durable', true),
                        (bool) config('rabbitmq.queue_exclusive', false),
                        (bool) config('rabbitmq.queue_auto_delete', false)
                    );

                    $bindingKey = (string) config('rabbitmq.binding_key', $routingKey);
                    $channel->queue_bind($queue, $exchange, $bindingKey);
                }

                $message = new AMQPMessage($messageBody, [
                    'content_type' => 'application/json',
                    'delivery_mode' => 2,
                ]);

                $channel->basic_publish($message, $exchange, $routingKey);
                return;
            } catch (\Throwable $e) {
                Log::error('RabbitMQ publish failed', [
                    'event_id' => $eventId,
                    'event_name' => $name,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt > $retries) {
                    throw $e;
                }

                if ($backoffMs > 0) {
                    usleep($backoffMs * 1000);
                }
            } finally {
                if ($channel !== null) {
                    $channel->close();
                }
                if ($connection !== null) {
                    $connection->close();
                }
            }
        }
    }

    private function resolveRoutingKey(string $eventName): string
    {
        $explicit = config('rabbitmq.routing_key');
        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        $normalized = str_replace(['\\', ' '], ['.', '_'], $eventName);
        $normalized = preg_replace('/[^a-zA-Z0-9._-]/', '', $normalized);
        return strtolower($normalized ?? $eventName);
    }
}
