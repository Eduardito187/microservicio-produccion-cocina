<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Bus;

use App\Infrastructure\Bus\RabbitMqEventBus;
use DateTimeImmutable;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

/**
 * @class RabbitMqEventBusTest
 */
class RabbitMqEventBusTest extends TestCase
{
    public function test_resolve_routing_key_prioritizes_mapped_queue(): void
    {
        $bus = new RabbitMqEventBus;

        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['MyEvent', 'mapped.queue']);

        $this->assertSame('mapped.queue', $result);
    }

    public function test_resolve_routing_key_uses_explicit_config_when_present(): void
    {
        config(['rabbitmq.routing_key' => 'configured.route']);

        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['My\\Event Name', null]);

        $this->assertSame('configured.route', $result);
    }

    public function test_resolve_routing_key_normalizes_event_name_when_no_config(): void
    {
        config(['rabbitmq.routing_key' => null]);

        $bus = new RabbitMqEventBus;
        $result = $this->invokePrivate($bus, 'resolveRoutingKey', ['My\\Event Name', null]);

        $this->assertSame('my.event_name', $result);
    }

    public function test_publish_throws_when_payload_cannot_be_json_encoded(): void
    {
        $bus = new RabbitMqEventBus;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to encode outbox message');

        $bus->publish(
            'evt-1',
            'EventName',
            ['invalid' => INF],
            new DateTimeImmutable('2026-04-06T12:00:00Z')
        );
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }
}
