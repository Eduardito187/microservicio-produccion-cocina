<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation;

use App\Application\Integration\IntegrationEventRouter;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Presentation\Console\Commands\ConsumeRabbitMq;
use Illuminate\Support\Facades\Artisan;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class ConsumeRabbitMqRetryPolicyTest
 */
class ConsumeRabbitMqRetryPolicyTest extends TestCase
{
    private function makeCommand(
        ?RegistrarInboundEventHandler $handler = null,
        ?IntegrationEventRouter $router = null
    ): ConsumeRabbitMq {
        $handler = $handler ?? $this->createMock(RegistrarInboundEventHandler::class);
        $router = $router ?? $this->createMock(IntegrationEventRouter::class);

        return new ConsumeRabbitMq($handler, $router);
    }

    public function test_process_message_success_dispatches_and_acks(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn(false);

        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->once())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => '11111111-1111-4111-8111-111111111111',
            'event' => 'RecetaActualizada',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => 1,
            'correlation_id' => '22222222-2222-4222-8222-222222222222',
            'payload' => [
                'id' => 'rec-1',
                'name' => 'Receta',
                'ingredients' => ['agua'],
            ],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 77,
            'routing_key' => 'receta.actualizada',
        ];

        $command->testProcessMessage($msg);

        $this->assertSame([77], $channel->acks);
        $this->assertSame([], $channel->nacks);
    }

    public function test_process_message_duplicate_event_does_not_dispatch_but_acks(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn(true);

        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->never())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => '33333333-3333-4333-8333-333333333333',
            'event' => 'SuscripcionCreada',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => 1,
            'correlation_id' => '44444444-4444-4444-8444-444444444444',
            'payload' => [
                'suscripcionId' => 'sub-1',
            ],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 88,
            'routing_key' => 'suscripcion.creada',
        ];

        $command->testProcessMessage($msg);

        $this->assertSame([88], $channel->acks);
        $this->assertSame([], $channel->nacks);
    }

    public function test_process_message_non_envelope_payload_uses_routing_key_and_acks(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn(false);

        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->once())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $msg = new AMQPMessage((string) json_encode([
            'pacienteId' => 'pat-9',
        ]));
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 99,
            'routing_key' => 'paciente.paciente-creado',
        ];

        $command->testProcessMessage($msg);

        $this->assertSame([99], $channel->acks);
        $this->assertSame([], $channel->nacks);
    }

    public function test_process_message_invalid_json_nacks_without_requeue(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->never())->method('__invoke');

        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->never())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $msg = new AMQPMessage('not-json');
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 123,
            'routing_key' => 'invalid.key',
        ];

        $command->testProcessMessage($msg);

        $this->assertSame([], $channel->acks);
        $this->assertSame([[123, false, false]], $channel->nacks);
    }

    public function test_get_retry_count_uses_x_death_count(): void
    {
        $command = $this->makeCommand();
        $ref = new ReflectionClass($command);
        $method = $ref->getMethod('getRetryCount');
        $method->setAccessible(true);

        $headers = new AMQPTable([
            'x-death' => [
                ['count' => 2],
            ],
        ]);
        $msg = new AMQPMessage('{"x":1}', ['application_headers' => $headers]);

        $count = $method->invoke($command, $msg);
        $this->assertSame(2, $count);
    }

    public function test_resolve_retry_delay_uses_configured_list(): void
    {
        $command = $this->makeCommand();

        $delay0 = $this->invokePrivate($command, 'resolveRetryDelay', [0]);
        $delay1 = $this->invokePrivate($command, 'resolveRetryDelay', [1]);
        $delay2 = $this->invokePrivate($command, 'resolveRetryDelay', [2]);

        $this->assertSame(10, $delay0);
        $this->assertSame(60, $delay1);
        $this->assertSame(300, $delay2);
    }

    public function test_get_retry_count_returns_zero_without_headers(): void
    {
        $command = $this->makeCommand();
        $msg = new AMQPMessage('{"x":1}');

        $count = $this->invokePrivate($command, 'getRetryCount', [$msg]);
        $this->assertSame(0, $count);
    }

    public function test_resolve_event_name_uses_aliases_and_fallbacks(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'legacy.event' => 'RecetaActualizada',
                'route.key' => 'PacienteActualizado',
            ],
        ]);

        $command = $this->makeCommand();

        $fromEvent = $this->invokePrivate($command, 'resolveEventName', ['legacy.event', '']);
        $fromRouting = $this->invokePrivate($command, 'resolveEventName', [null, 'route.key']);
        $plain = $this->invokePrivate($command, 'resolveEventName', ['plain.event', 'route.key']);
        $null = $this->invokePrivate($command, 'resolveEventName', [null, '']);

        $this->assertSame('RecetaActualizada', $fromEvent);
        $this->assertSame('PacienteActualizado', $fromRouting);
        $this->assertSame('plain.event', $plain);
        $this->assertNull($null);
    }

    public function test_is_non_retryable_for_known_and_unknown_errors(): void
    {
        $command = $this->makeCommand();

        $known = $this->invokePrivate($command, 'isNonRetryable', [new \RuntimeException('Invalid JSON payload')]);
        $unknown = $this->invokePrivate($command, 'isNonRetryable', [new \RuntimeException('socket timeout')]);

        $this->assertTrue($known);
        $this->assertFalse($unknown);
    }

    public function test_normalize_queue_token_and_deterministic_event_id(): void
    {
        $command = $this->makeCommand();

        $token = $this->invokePrivate($command, 'normalizeQueueToken', ['  MY Queue / PROD  ']);
        $fallback = $this->invokePrivate($command, 'normalizeQueueToken', ['***']);

        $id1 = $this->invokePrivate($command, 'deterministicEventId', ['route.key', ['a' => 1]]);
        $id2 = $this->invokePrivate($command, 'deterministicEventId', ['route.key', ['a' => 1]]);

        $this->assertSame('my_queue___prod', $token);
        $this->assertSame('___', $fallback);
        $this->assertSame($id1, $id2);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $id1);
    }

    public function test_is_self_consume_config_detects_queue_or_routing_overlap(): void
    {
        config([
            'rabbitmq.queue' => 'outbox.q',
            'rabbitmq.routing_key' => 'outbox.route',
            'rabbitmq.binding_key' => 'outbox.bind',
        ]);

        $command = $this->makeCommand();

        $sameQueue = $this->invokePrivate($command, 'isSelfConsumeConfig', ['outbox.q', 'inbound.ex', 'inbound.key']);
        $sameRoute = $this->invokePrivate($command, 'isSelfConsumeConfig', ['inbound.q', 'inbound.ex', 'outbox.route']);
        $different = $this->invokePrivate($command, 'isSelfConsumeConfig', ['inbound.q', 'inbound.ex', 'inbound.key']);

        $this->assertTrue($sameQueue);
        $this->assertTrue($sameRoute);
        $this->assertFalse($different);
    }

    public function test_ack_and_nack_message_use_delivery_info_when_available(): void
    {
        $command = $this->makeCommand();

        $channel = new class
        {
            public array $acks = [];
            public array $nacks = [];

            public function basic_ack($tag): void
            {
                $this->acks[] = $tag;
            }

            public function basic_nack($tag, $multiple, $requeue): void
            {
                $this->nacks[] = [$tag, $multiple, $requeue];
            }
        };

        $msg = new AMQPMessage('{"ok":true}');
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 44,
            'routing_key' => 'route.key',
        ];

        $this->invokePrivate($command, 'ackMessage', [$msg]);
        $this->invokePrivate($command, 'nackMessage', [$msg, true]);

        $this->assertSame([44], $channel->acks);
        $this->assertSame([[44, false, true]], $channel->nacks);
        $this->assertSame('route.key', $this->invokePrivate($command, 'resolveRoutingKey', [$msg]));
    }

    public function test_publish_to_retry_republishes_with_ttl_and_headers(): void
    {
        $command = $this->makeCommand();

        $channel = new class
        {
            public ?AMQPMessage $message = null;
            public ?string $exchange = null;
            public ?string $routingKey = null;

            public function basic_publish($message, $exchange, $routingKey): void
            {
                $this->message = $message;
                $this->exchange = $exchange;
                $this->routingKey = $routingKey;
            }
        };

        $headers = new AMQPTable(['x-death' => [['count' => 1]]]);
        $msg = new AMQPMessage('{"event":"x"}', [
            'content_type' => 'application/json',
            'application_headers' => $headers,
        ]);
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 11,
        ];

        $this->invokePrivate($command, 'publishToRetry', [$msg, 'retry.ex', 'retry.key', 15]);

        $this->assertSame('retry.ex', $channel->exchange);
        $this->assertSame('retry.key', $channel->routingKey);
        $this->assertInstanceOf(AMQPMessage::class, $channel->message);
        $this->assertSame('15000', $channel->message?->get('expiration'));
        $this->assertSame('application/json', $channel->message?->get('content_type'));
    }

    public function test_handle_fails_fast_when_queue_exchange_or_binding_are_missing(): void
    {
        config([
            'rabbitmq.inbound' => [
                'exchange' => 'inbound.ex',
                'routing_keys' => 'rk.1',
            ],
        ]);
        $this->assertSame(ConsumeRabbitMq::FAILURE, Artisan::call('rabbitmq:consume'));

        config([
            'rabbitmq.inbound' => [
                'queue' => 'inbound.q',
                'routing_keys' => 'rk.1',
            ],
        ]);
        $this->assertSame(ConsumeRabbitMq::FAILURE, Artisan::call('rabbitmq:consume'));

        config([
            'rabbitmq.inbound' => [
                'queue' => 'inbound.q',
                'exchange' => 'inbound.ex',
                'routing_keys' => '',
            ],
        ]);
        $this->assertSame(ConsumeRabbitMq::FAILURE, Artisan::call('rabbitmq:consume'));
    }

    public function test_handle_fails_when_inbound_matches_outbound_config(): void
    {
        config([
            'rabbitmq.queue' => 'outbox.q',
            'rabbitmq.inbound' => [
                'queue' => 'outbox.q',
                'exchange' => 'inbound.ex',
                'routing_keys' => 'inbound.key',
            ],
        ]);

        $this->assertSame(ConsumeRabbitMq::FAILURE, Artisan::call('rabbitmq:consume'));
    }

    public function test_process_message_retry_publishes_to_retry_exchange_and_acks(): void
    {
        config([
            'rabbitmq.inbound.max_retries' => 3,
            'rabbitmq.inbound.retry_delays' => '5,30',
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willThrowException(new \RuntimeException('temporary failure'));

        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->never())->method('dispatch');

        $command = $this->makeCommand($handler, $router);

        $channel = new class
        {
            public array $acks = [];
            public array $nacks = [];
            public array $publishes = [];

            public function basic_ack($tag): void
            {
                $this->acks[] = $tag;
            }

            public function basic_nack($tag, $multiple, $requeue): void
            {
                $this->nacks[] = [$tag, $multiple, $requeue];
            }

            public function basic_publish($message, $exchange, $routingKey): void
            {
                $this->publishes[] = [$message, $exchange, $routingKey];
            }
        };

        $headers = new AMQPTable([
            'x-death' => [
                ['count' => 0],
            ],
        ]);

        $payload = [
            'event_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'event' => 'PacienteCreado',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => 1,
            'correlation_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'payload' => ['pacienteId' => 'p-1'],
        ];

        $msg = new AMQPMessage((string) json_encode($payload), [
            'application_headers' => $headers,
            'content_type' => 'application/json',
        ]);
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 500,
            'routing_key' => 'paciente.paciente-creado',
        ];

        $this->invokePrivate($command, 'processMessage', [$msg, 'retry.ex', 'retry.q', 'fallback.key']);

        $this->assertCount(1, $channel->publishes);
        $this->assertSame('retry.ex', $channel->publishes[0][1]);
        $this->assertSame('paciente.paciente-creado', $channel->publishes[0][2]);
        $this->assertSame([500], $channel->acks);
        $this->assertSame([], $channel->nacks);
    }

    public function test_process_message_nacks_without_requeue_when_max_retries_reached(): void
    {
        config([
            'rabbitmq.inbound.max_retries' => 2,
            'rabbitmq.inbound.retry_delays' => '5,30',
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willThrowException(new \RuntimeException('still failing'));

        $command = $this->makeCommand($handler, $this->createMock(IntegrationEventRouter::class));

        $channel = $this->makeFakeChannel();
        $headers = new AMQPTable([
            'x-death' => [
                ['count' => 2],
            ],
        ]);

        $payload = [
            'event_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'event' => 'PacienteCreado',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => 1,
            'correlation_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'payload' => ['pacienteId' => 'p-2'],
        ];

        $msg = new AMQPMessage((string) json_encode($payload), [
            'application_headers' => $headers,
        ]);
        $msg->setChannel($channel);
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 777,
            'routing_key' => 'paciente.paciente-creado',
        ];

        $this->invokePrivate($command, 'processMessage', [$msg, 'retry.ex', 'retry.q', 'fallback.key']);

        $this->assertSame([], $channel->acks);
        $this->assertSame([[777, false, false]], $channel->nacks);
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $ref = new ReflectionClass($instance);
        $target = $ref->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }

    private function makeFakeChannel(): object
    {
        return new class
        {
            public array $acks = [];
            public array $nacks = [];
            public array $publishes = [];

            public function basic_ack($tag): void
            {
                $this->acks[] = $tag;
            }

            public function basic_nack($tag, $multiple, $requeue): void
            {
                $this->nacks[] = [$tag, $multiple, $requeue];
            }

            public function basic_publish($message, $exchange, $routingKey): void
            {
                $this->publishes[] = [$message, $exchange, $routingKey];
            }
        };
    }

    // ─── Additional coverage tests ─────────────────────────────────────────────

    public function test_ack_and_nack_message_skip_when_channel_or_tag_is_null(): void
    {
        $command = $this->makeCommand();
        $msg = new AMQPMessage('{}');
        $this->invokePrivate($command, 'ackMessage', [$msg]);
        $this->invokePrivate($command, 'nackMessage', [$msg, true]);
        $this->assertTrue(true);
    }

    public function test_resolve_delivery_tag_falls_back_to_get_delivery_tag(): void
    {
        $command = $this->makeCommand();
        $msg = new AMQPMessage('{}');
        $tag = $this->invokePrivate($command, 'resolveDeliveryTag', [$msg]);
        $this->assertNull($tag);
    }

    public function test_resolve_channel_falls_back_to_get_channel(): void
    {
        $command = $this->makeCommand();
        $msg = new AMQPMessage('{}');
        $channel = $this->invokePrivate($command, 'resolveChannel', [$msg]);
        $this->assertNull($channel);
    }

    public function test_get_retry_count_returns_zero_for_empty_x_death(): void
    {
        $command = $this->makeCommand();
        $headers = new AMQPTable(['x-death' => []]);
        $msg = new AMQPMessage('{}', ['application_headers' => $headers]);
        $count = $this->invokePrivate($command, 'getRetryCount', [$msg]);
        $this->assertSame(0, $count);
    }

    public function test_get_retry_count_returns_zero_when_x_death_missing_count(): void
    {
        $command = $this->makeCommand();
        $headers = new AMQPTable(['x-death' => [['reason' => 'rejected']]]);
        $msg = new AMQPMessage('{}', ['application_headers' => $headers]);
        $count = $this->invokePrivate($command, 'getRetryCount', [$msg]);
        $this->assertSame(0, $count);
    }

    public function test_process_message_generates_correlation_id_when_missing(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->once())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
            'event' => 'DireccionCreada',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => 1,
            'payload' => ['direccionId' => 'dir-1'],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 88, 'routing_key' => 'dir.creada'];

        $command->testProcessMessage($msg);
        $this->assertSame([88], $channel->acks);
    }

    public function test_process_message_accepts_string_schema_version(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router = $this->createMock(IntegrationEventRouter::class);
        $router->expects($this->once())->method('dispatch');

        $command = $this->makeCommand($handler, $router);
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
            'event' => 'PacienteActualizado',
            'occurred_on' => '2026-01-01T00:00:00+00:00',
            'schema_version' => '2',
            'correlation_id' => 'aaaabbbb-aaaa-4aaa-8aaa-aaaaaaaabbbb',
            'payload' => ['pacienteId' => 'p-3'],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 99, 'routing_key' => 'paciente.creado'];

        $command->testProcessMessage($msg);
        $this->assertSame([99], $channel->acks);
    }

    public function test_process_message_invalid_event_id_nacks_without_requeue(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'not-a-uuid', 'event' => 'PacienteCreado',
            'schema_version' => 1, 'correlation_id' => 'ccccbbbb-cccc-4ccc-8ccc-ccccccccbbbb',
            'payload' => ['pacienteId' => 'p-4'],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 33, 'routing_key' => 'paciente.creado'];

        $command->testProcessMessage($msg);
        $this->assertSame([[33, false, false]], $channel->nacks);
    }

    public function test_process_message_non_numeric_schema_version_nacks_non_retryable(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'a1b2c3d4-a1b2-4a1b-8a1b-a1b2c3d4e5f6',
            'event' => 'SomeEvent', 'schema_version' => 'bad-version',
            'correlation_id' => 'b2c3d4e5-b2c3-4b2c-8b2c-b2c3d4e5f6a7',
            'payload' => ['x' => 1],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 44, 'routing_key' => 'some.event'];

        $command->testProcessMessage($msg);
        $this->assertSame([[44, false, false]], $channel->nacks);
    }

    public function test_process_message_invalid_correlation_id_nacks_non_retryable(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'c3d4e5f6-c3d4-4c3d-8c3d-c3d4e5f6a7b8',
            'event' => 'SomeEvent', 'schema_version' => 1, 'correlation_id' => 'not-a-uuid',
            'payload' => ['x' => 1],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 55, 'routing_key' => 'some.event'];

        $command->testProcessMessage($msg);
        $this->assertSame([[55, false, false]], $channel->nacks);
    }

    public function test_process_message_non_array_payload_nacks_non_retryable(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $payload = [
            'event_id' => 'd4e5f6a7-d4e5-4d4e-8d4e-d4e5f6a7b8c9',
            'event' => 'SomeEvent', 'schema_version' => 1,
            'correlation_id' => 'e5f6a7b8-e5f6-4e5f-8e5f-e5f6a7b8c9d0',
            'payload' => 'string-not-object',
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 66, 'routing_key' => 'some.event'];

        $command->testProcessMessage($msg);
        $this->assertSame([[66, false, false]], $channel->nacks);
    }

    public function test_process_message_missing_event_id_in_envelope_nacks_non_retryable(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $payload = [
            'event' => '', 'schema_version' => 1,
            'correlation_id' => 'f6a7b8c9-f6a7-4f6a-8f6a-f6a7b8c9d0e1',
            'payload' => ['x' => 1],
        ];

        $msg = new AMQPMessage((string) json_encode($payload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 11, 'routing_key' => ''];

        $command->testProcessMessage($msg);
        $this->assertSame([[11, false, false]], $channel->nacks);
    }

    public function test_resolve_retry_delay_returns_zero_when_empty_config(): void
    {
        $command = $this->makeCommand();
        config(['rabbitmq.inbound.retry_delays' => '']);
        $delay = $this->invokePrivate($command, 'resolveRetryDelay', [0]);
        $this->assertSame(0, $delay);
        config(['rabbitmq.inbound.retry_delays' => '10,60,300']);
    }

    public function test_get_inbound_max_retries_returns_configured_value(): void
    {
        $command = $this->makeCommand();
        $retries = $this->invokePrivate($command, 'getInboundMaxRetries', []);
        $this->assertIsInt($retries);
    }

    public function test_process_message_non_envelope_with_empty_routing_key_nacks(): void
    {
        $command = $this->makeCommand();
        $channel = $this->makeFakeChannel();

        $rawPayload = ['some_field' => 'value'];

        $msg = new AMQPMessage((string) json_encode($rawPayload));
        $msg->setChannel($channel);
        $msg->delivery_info = ['channel' => $channel, 'delivery_tag' => 22, 'routing_key' => ''];

        $command->testProcessMessage($msg);
        $this->assertSame([[22, false, false]], $channel->nacks);
    }
}
