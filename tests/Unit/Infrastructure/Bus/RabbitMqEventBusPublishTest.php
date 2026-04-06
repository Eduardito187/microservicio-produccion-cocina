<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Bus;

use App\Infrastructure\Bus\RabbitMqEventBus;

use DateTimeImmutable;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class RabbitMqEventBusPublishTest
 */
class RabbitMqEventBusPublishTest extends TestCase
{
    public function test_publish_throws_after_retry_attempts_when_connection_fails(): void
    {
        config([
            'rabbitmq.host' => 'invalid-host-for-tests.local',
            'rabbitmq.port' => 5672,
            'rabbitmq.user' => 'guest',
            'rabbitmq.password' => 'guest',
            'rabbitmq.vhost' => '/',
            'rabbitmq.connect_timeout' => 1,
            'rabbitmq.read_write_timeout' => 1,
            'rabbitmq.exchange' => 'ex.test',
            'rabbitmq.exchange_type' => 'direct',
            'rabbitmq.exchange_durable' => false,
            'rabbitmq.queue' => 'q.test',
            'rabbitmq.binding_key' => 'binding.key',
            'rabbitmq.publish_retries' => 1,
            'rabbitmq.publish_backoff_ms' => 0,
        ]);

        $bus = new RabbitMqEventBus;

        $this->expectException(\Throwable::class);

        $bus->publish(
            '11111111-1111-4111-8111-111111111111',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            ['id' => 'op-1'],
            new DateTimeImmutable('2026-04-06T00:00:00Z'),
            [
                'schema_version' => 1,
                'correlation_id' => '22222222-2222-4222-8222-222222222222',
                'aggregate_id' => 'agg-1',
            ]
        );
    }

    private function invokePrivate(object $instance, string $method, array $args = []): mixed
    {
        $ref = new ReflectionClass($instance);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($instance, $args);
    }

    public function test_resolve_routing_key_uses_mapped_queue_name(): void
    {
        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['MyEvent', 'my.mapped.queue']);
        $this->assertSame('my.mapped.queue', $result);
    }

    public function test_resolve_routing_key_uses_explicit_routing_key_config(): void
    {
        config(['rabbitmq.routing_key' => 'explicit.key']);
        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['AnyEvent', null]);
        $this->assertSame('explicit.key', $result);
        config(['rabbitmq.routing_key' => null]);
    }

    public function test_resolve_routing_key_normalizes_event_name_when_no_config(): void
    {
        config(['rabbitmq.routing_key' => '']);
        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['App\\Domain\\SomeEvent', null]);
        $this->assertSame('app.domain.someevent', $result);
        config(['rabbitmq.routing_key' => null]);
    }

    public function test_resolve_routing_key_removes_special_chars(): void
    {
        config(['rabbitmq.routing_key' => '']);
        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['My Event#2', null]);
        // spaces become underscores, # removed, then lowercased
        $this->assertStringNotContainsString('#', $result);
        $this->assertStringNotContainsString(' ', $result);
        config(['rabbitmq.routing_key' => null]);
    }

    public function test_publish_retries_multiple_times_before_throwing(): void
    {
        config([
            'rabbitmq.host' => 'invalid-host-2.local',
            'rabbitmq.port' => 5672,
            'rabbitmq.user' => 'guest',
            'rabbitmq.password' => 'guest',
            'rabbitmq.vhost' => '/',
            'rabbitmq.connect_timeout' => 1,
            'rabbitmq.read_write_timeout' => 1,
            'rabbitmq.exchange' => '',         // empty exchange → skip exchange_declare
            'rabbitmq.queue' => '',            // empty queue → skip queue_declare
            'rabbitmq.publish_retries' => 2,
            'rabbitmq.publish_backoff_ms' => 0,
            'rabbitmq.event_queues' => ['MyEvent' => 'specific.queue'],
        ]);

        $bus = new RabbitMqEventBus;

        $this->expectException(\Throwable::class);

        $bus->publish(
            '33333333-3333-4333-8333-333333333333',
            'MyEvent',
            ['data' => 'test'],
            new DateTimeImmutable('2026-05-01T00:00:00Z'),
            ['schema_version' => 1, 'correlation_id' => '44444444-4444-4444-8444-444444444444', 'aggregate_id' => null]
        );
    }
}
