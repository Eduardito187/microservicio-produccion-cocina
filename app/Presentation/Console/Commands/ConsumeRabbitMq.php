<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Console\Commands;

use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Integration\IntegrationEventRouter;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Console\Command;
use PhpAmqpLib\Wire\AMQPTable;
use Illuminate\Support\Str;
use DateTimeImmutable;

/**
 * @class ConsumeRabbitMq
 * @package App\Presentation\Console\Commands
 */
class ConsumeRabbitMq extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume
        {queue? : Queue name (defaults to RABBITMQ_QUEUE)}
        {--binding-key= : Routing key (defaults to RABBITMQ_BINDING_KEY or RABBITMQ_ROUTING_KEY)}
        {--prefetch=10 : Prefetch count}
        {--once : Consume a single message and exit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from RabbitMQ using INBOUND_RABBITMQ_* configuration';

    /**
     * @var RegistrarInboundEventHandler
     */
    private $registrarInboundEventHandler;

    /**
     * @var IntegrationEventRouter
     */
    private $integrationEventRouter;

    /**
     * Constructor
     *
     * @param RegistrarInboundEventHandler $registrarInboundEventHandler
     * @param IntegrationEventRouter $integrationEventRouter
     */
    public function __construct(
        RegistrarInboundEventHandler $registrarInboundEventHandler,
        IntegrationEventRouter $integrationEventRouter
    ) {
        $this->registrarInboundEventHandler = $registrarInboundEventHandler;
        $this->integrationEventRouter = $integrationEventRouter;
        parent::__construct();
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $inbound = config('rabbitmq.inbound', []);
        $queue = (string) ($this->argument('queue') ?: ($inbound['queue'] ?? ''));
        $exchange = (string) ($inbound['exchange'] ?? '');
        $exchangeType = (string) config('rabbitmq.exchange_type', 'fanout');
        $bindingKey = (string) ($this->option('binding-key') ?: ($inbound['routing_keys'] ?? ''));
        $prefetch = (int) $this->option('prefetch');
        $once = (bool) $this->option('once');

        if ($queue === '') {
            logger()->error('Inbound consumer misconfigured (missing INBOUND_RABBITMQ_QUEUE)');
            $this->error('INBOUND_RABBITMQ_QUEUE is required for inbound consumer.');
            return self::FAILURE;
        }

        if ($exchange === '') {
            logger()->error('Inbound consumer misconfigured (missing INBOUND_RABBITMQ_EXCHANGE)');
            $this->error('INBOUND_RABBITMQ_EXCHANGE is required for inbound consumer.');
            return self::FAILURE;
        }
        if ($this->isSelfConsumeConfig($queue, $exchange, $bindingKey)) {
            logger()->error('Inbound consumer misconfigured (inbound matches outbound configuration)', [
                'inbound_queue' => $queue,
                'inbound_exchange' => $exchange,
                'inbound_routing_keys' => $bindingKey,
            ]);
            $this->error('Inbound configuration must not match outbox exchange/queue.');
            return self::FAILURE;
        }

        $connection = new AMQPStreamConnection(
            (string) config('rabbitmq.host'),
            (int) config('rabbitmq.port'),
            (string) config('rabbitmq.user'),
            (string) config('rabbitmq.password'),
            (string) config('rabbitmq.vhost'),
            false,
            'AMQPLAIN',
            null,
            'en_US',
            (int) config('rabbitmq.connect_timeout', 3),
            (int) config('rabbitmq.read_write_timeout', 3)
        );

        $channel = $connection->channel();
        $channel->basic_qos(null, max($prefetch, 1), null);

        $channel->exchange_declare(
            $exchange,
            $exchangeType,
            false,
            (bool) config('rabbitmq.exchange_durable', true),
            false
        );

        $dlx = (string) ($inbound['dlx'] ?? '');
        $dlq = (string) ($inbound['dlq'] ?? '');
        $dlqRoutingKey = (string) ($inbound['dlq_routing_key'] ?? '');
        $retryExchange = (string) ($inbound['retry_exchange'] ?? '');
        $retryQueue = (string) ($inbound['retry_queue'] ?? '');
        $retryRoutingKey = (string) ($inbound['retry_routing_key'] ?? '');

        if ($dlx !== '' && $dlq !== '') {
            $channel->exchange_declare(
                $dlx,
                'direct',
                false,
                true,
                false
            );
            $channel->queue_declare(
                $dlq,
                false,
                true,
                false,
                false
            );
            $channel->queue_bind($dlq, $dlx, $dlqRoutingKey !== '' ? $dlqRoutingKey : $dlq);
        }

        $retryExchange = $retryExchange !== '' ? $retryExchange : ($exchange !== '' ? $exchange . '.retry' : '');
        $retryQueue = $retryQueue !== '' ? $retryQueue : ($queue !== '' ? $queue . '.retry' : '');
        $retryRoutingKey = $retryRoutingKey !== '' ? $retryRoutingKey : $queue;

        if ($retryExchange !== '' && $retryQueue !== '') {
            $channel->exchange_declare(
                $retryExchange,
                'direct',
                false,
                true,
                false
            );
            $retryArgs = new AMQPTable([
                'x-dead-letter-exchange' => $exchange,
            ]);
            $channel->queue_declare(
                $retryQueue,
                false,
                true,
                false,
                false,
                false,
                $retryArgs
            );
            $channel->queue_bind($retryQueue, $retryExchange, $retryRoutingKey);
        }

        $queueArgs = [];
        if ($dlx !== '') {
            $queueArgs['x-dead-letter-exchange'] = $dlx;
            if ($dlqRoutingKey !== '') {
                $queueArgs['x-dead-letter-routing-key'] = $dlqRoutingKey;
            }
        }

        $channel->queue_declare(
            $queue,
            false,
            (bool) config('rabbitmq.queue_durable', true),
            (bool) config('rabbitmq.queue_exclusive', false),
            (bool) config('rabbitmq.queue_auto_delete', false),
            false,
            $queueArgs === [] ? null : new AMQPTable($queueArgs)
        );

        $keys = array_filter(array_map('trim', explode(',', (string) $bindingKey)));
        if ($keys === []) {
            logger()->error('Inbound consumer misconfigured (missing INBOUND_RABBITMQ_ROUTING_KEYS)');
            $this->error('INBOUND_RABBITMQ_ROUTING_KEYS is required for inbound consumer.');
            return self::FAILURE;
        }
        foreach ($keys as $key) {
            $channel->queue_bind($queue, $exchange, $key);
        }

        $this->info("Consuming from queue={$queue} exchange={$exchange} bindingKey={$bindingKey}");

        $channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $msg) use ($once, $retryExchange, $retryQueue, $retryRoutingKey): void {
            $this->processMessage($msg, $retryExchange, $retryQueue, $retryRoutingKey);
            if ($once) {
                $msg->getChannel()->basic_cancel($msg->getConsumerTag());
            }
        });

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }

    /**
     * @param AMQPMessage $msg
     * @return void
     */
    public function testProcessMessage(AMQPMessage $msg): void
    {
        $this->processMessage($msg, '', '', '');
    }

    /**
     * @param AMQPMessage $msg
     * @param string $retryExchange
     * @param string $retryQueue
     * @param string $retryRoutingKey
     * @return void
     */
    private function processMessage(AMQPMessage $msg, string $retryExchange, string $retryQueue, string $retryRoutingKey): void
    {
        $payload = $msg->getBody();
        $decoded = null;
        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);
        }

        $eventId = null;
        $eventName = null;
        $correlationId = null;

        try {
            if (!is_array($decoded)) {
                throw new \RuntimeException('Invalid JSON payload');
            }

            $eventId = $decoded['event_id'] ?? null;
            $eventName = $decoded['event'] ?? ($decoded['event_name'] ?? null);
            $occurredOn = $decoded['occurred_on'] ?? null;
            $eventPayload = $decoded['payload'] ?? null;
            $correlationId = $decoded['correlation_id'] ?? ($decoded['correlationId'] ?? null);
            $schemaVersion = $decoded['schema_version'] ?? ($decoded['schemaVersion'] ?? null);
            $aggregateId = $decoded['aggregate_id'] ?? ($decoded['aggregateId'] ?? null);

            logger()->info('RabbitMQ message received', [
                'routing_key' => $msg->getRoutingKey(),
                'event_id' => $eventId,
                'event_name' => $eventName,
                'correlation_id' => $correlationId,
                'payload' => $eventPayload ?? $payload,
            ]);

            if (!is_string($eventId) || $eventId === '' || !is_string($eventName) || $eventName === '') {
                throw new \RuntimeException('Missing event_id or event name');
            }
            if (!Str::isUuid($eventId)) {
                throw new \RuntimeException('event_id must be a UUID');
            }
            if ($schemaVersion === null || $schemaVersion === '') {
                throw new \RuntimeException('schema_version is required');
            }
            if (!is_int($schemaVersion) && !(is_string($schemaVersion) && ctype_digit($schemaVersion))) {
                throw new \RuntimeException('schema_version must be an integer');
            }
            if ($correlationId === null || $correlationId === '') {
                $correlationId = (string) Str::uuid();
                logger()->warning('correlation_id missing; generated new one', [
                    'event_id' => $eventId,
                    'event_name' => $eventName,
                    'correlation_id' => $correlationId,
                ]);
            } elseif (!Str::isUuid($correlationId)) {
                throw new \RuntimeException('correlation_id must be a UUID');
            }
            if (!is_array($eventPayload)) {
                throw new \RuntimeException('payload must be an object');
            }
            $this->validatePayload($eventName, $eventPayload);

            $payloadJson = json_encode($eventPayload);
            if (!is_string($payloadJson)) {
                throw new \RuntimeException('Unable to encode payload');
            }

            // Register inbound event for idempotency
            $command = new RegistrarInboundEvent(
                $eventId,
                $eventName,
                is_string($occurredOn) ? $occurredOn : (new DateTimeImmutable('now'))->format(DATE_ATOM),
                $payloadJson,
                is_int($schemaVersion) ? $schemaVersion : (is_string($schemaVersion) && is_numeric($schemaVersion) ? (int) $schemaVersion : null),
                is_string($correlationId) && $correlationId !== '' ? $correlationId : null
            );

            $isDuplicate = $this->registrarInboundEventHandler->__invoke($command);
            if (!$isDuplicate) {
                $this->integrationEventRouter->dispatch(
                    $eventName,
                    is_array($eventPayload) ? $eventPayload : [],
                    [
                        'event_id' => $eventId,
                        'occurred_on' => $occurredOn,
                        'routing_key' => $msg->getRoutingKey(),
                        'correlation_id' => $correlationId,
                        'schema_version' => $schemaVersion,
                        'aggregate_id' => $aggregateId,
                    ]
                );
            }
            $msg->ack();
        } catch (\Throwable $e) {
            $maxRetries = $this->getInboundMaxRetries();
            $retryCount = $this->getRetryCount($msg);
            $shouldRequeue = $retryCount < $maxRetries;
            if ($this->isNonRetryable($e)) {
                $shouldRequeue = false;
            }

            logger()->error('RabbitMQ message handling failed', [
                'event_id' => $eventId ?? null,
                'event_name' => $eventName ?? null,
                'correlation_id' => $correlationId ?? null,
                'retry_count' => $retryCount,
                'max_retries' => $maxRetries,
                'error' => $e->getMessage(),
            ]);
            if ($shouldRequeue) {
                $delay = $this->resolveRetryDelay($retryCount);
                if ($delay > 0 && $retryExchange !== '' && $retryQueue !== '') {
                    $this->publishToRetry($msg, $retryExchange, $retryRoutingKey, $delay);
                    $msg->ack();
                    return;
                }
            }
            // Retry with requeue until max_retries; then dead-letter (if configured) or drop.
            $msg->getChannel()->basic_nack($msg->getDeliveryTag(), false, $shouldRequeue);
        }
    }

    /**
     * @param AMQPMessage $msg
     * @return int
     */
    private function getRetryCount(AMQPMessage $msg): int
    {
        $headers = $msg->get('application_headers');
        if (!$headers instanceof AMQPTable) {
            return 0;
        }

        $data = $headers->getNativeData();
        $xDeath = $data['x-death'] ?? null;
        if (!is_array($xDeath) || $xDeath === []) {
            return 0;
        }

        $first = $xDeath[0] ?? null;
        if (!is_array($first) || !isset($first['count'])) {
            return 0;
        }

        return (int) $first['count'];
    }

    /**
     * @param int $retryCount
     * @return int
     */
    private function resolveRetryDelay(int $retryCount): int
    {
        $raw = '10,60,300';
        if (function_exists('config')) {
            try {
                $raw = (string) config('rabbitmq.inbound.retry_delays', '10,60,300');
            } catch (\Throwable $e) {
                $raw = '10,60,300';
            }
        }
        $delays = array_values(array_filter(array_map('trim', explode(',', $raw))));
        $ints = array_map('intval', $delays);
        $ints = array_values(array_filter($ints, fn ($v) => $v > 0));
        if ($ints === []) {
            return 0;
        }
        $index = min($retryCount, count($ints) - 1);
        return $ints[$index];
    }

    /**
     * @return int
     */
    private function getInboundMaxRetries(): int
    {
        if (function_exists('config')) {
            try {
                return (int) config('rabbitmq.inbound.max_retries', 3);
            } catch (\Throwable $e) {
                return 3;
            }
        }
        return 3;
    }

    /**
     * @param AMQPMessage $msg
     * @param string $retryExchange
     * @param string $retryRoutingKey
     * @param int $delaySeconds
     * @return void
     */
    private function publishToRetry(AMQPMessage $msg, string $retryExchange, string $retryRoutingKey, int $delaySeconds): void
    {
        $headers = $msg->get('application_headers');
        $properties = [
            'content_type' => $msg->get('content_type') ?? 'application/json',
            'delivery_mode' => 2,
            'expiration' => (string) ($delaySeconds * 1000),
        ];
        if ($headers instanceof AMQPTable) {
            $properties['application_headers'] = $headers;
        }
        $retryMessage = new AMQPMessage($msg->getBody(), $properties);
        $msg->getChannel()->basic_publish($retryMessage, $retryExchange, $retryRoutingKey);
        logger()->info('RabbitMQ message scheduled for retry', [
            'retry_exchange' => $retryExchange,
            'retry_routing_key' => $retryRoutingKey,
            'delay_seconds' => $delaySeconds,
        ]);
    }

    /**
     * @param string $queue
     * @param string $exchange
     * @param string $bindingKeys
     * @return bool
     */
    private function isSelfConsumeConfig(string $queue, string $exchange, string $bindingKeys): bool
    {
        $outboxExchange = (string) config('rabbitmq.exchange', '');
        $outboxQueue = (string) config('rabbitmq.queue', '');
        $outboxRoutingKey = (string) config('rabbitmq.routing_key', '');
        $outboxBindingKey = (string) config('rabbitmq.binding_key', '');

        if ($outboxExchange !== '' && $exchange === $outboxExchange) {
            return true;
        }
        if ($outboxQueue !== '' && $queue === $outboxQueue) {
            return true;
        }

        $inboundKeys = array_filter(array_map('trim', explode(',', $bindingKeys)));
        $outKeys = array_filter(array_map('trim', [$outboxRoutingKey, $outboxBindingKey]));

        foreach ($inboundKeys as $key) {
            if (in_array($key, $outKeys, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $eventName
     * @param array $payload
     * @return void
     */
    private function validatePayload(string $eventName, array $payload): void
    {
        $requirements = [
            'DireccionCreada' => ['direccionId'],
            'DireccionActualizada' => ['direccionId'],
            'DireccionGeocodificada' => ['direccionId'],
            'PacienteCreado' => ['pacienteId'],
            'PacienteActualizado' => ['pacienteId'],
            'SuscripcionCreada' => ['suscripcionId'],
            'SuscripcionActualizada' => ['suscripcionId'],
            'RecetaActualizada' => ['recetaVersionId'],
            'CalendarioEntregaCreado' => ['calendarioId', 'fecha', 'sucursalId'],
            'EntregaProgramada' => ['calendarioId', 'itemDespachoId'],
            'DiaSinEntregaMarcado' => ['calendarioId'],
            'DireccionEntregaCambiada' => ['direccionId'],
            'EntregaConfirmada' => ['paqueteId'],
            'EntregaFallida' => ['paqueteId'],
            'PaqueteEnRuta' => ['paqueteId'],
        ];

        $required = $requirements[$eventName] ?? [];
        foreach ($required as $key) {
            if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
                throw new \RuntimeException("payload missing required field: {$key}");
            }
        }
    }

    /**
     * @param \Throwable $e
     * @return bool
     */
    private function isNonRetryable(\Throwable $e): bool
    {
        $message = $e->getMessage();
        if (!is_string($message)) {
            return false;
        }
        return str_contains($message, 'payload missing required field')
            || str_contains($message, 'payload must be an object')
            || str_contains($message, 'event_id must be a UUID')
            || str_contains($message, 'schema_version is required')
            || str_contains($message, 'schema_version must be an integer')
            || str_contains($message, 'correlation_id must be a UUID')
            || str_contains($message, 'Missing event_id or event name')
            || str_contains($message, 'Invalid JSON payload');
    }
}
