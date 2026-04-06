<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Bus;

use App\Infrastructure\Bus\HttpEventBus;
use DateTimeImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @class HttpEventBusTest
 */
class HttpEventBusTest extends TestCase
{
    public function test_publish_sends_expected_envelope_and_headers(): void
    {
        putenv('EVENTBUS_ENDPOINT=http://eventbus.test/api/event-bus');
        putenv('EVENTBUS_SECRET=secret-token');
        putenv('EVENTBUS_TIMEOUT=5');

        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $bus = new HttpEventBus;
        $occurredOn = new DateTimeImmutable('2026-04-06T01:02:03+00:00');

        $bus->publish(
            '11111111-1111-4111-8111-111111111111',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            ['id' => 'agg-1', 'qty' => 2],
            $occurredOn,
            [
                'schema_version' => 2,
                'correlation_id' => '22222222-2222-4222-8222-222222222222',
                'aggregate_id' => '33333333-3333-4333-8333-333333333333',
            ]
        );

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->url() === 'http://eventbus.test/api/event-bus'
                && $request->hasHeader('X-EventBus-Token', 'secret-token')
                && $data['event_id'] === '11111111-1111-4111-8111-111111111111'
                && $data['event'] === 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada'
                && $data['schema_version'] === 2
                && $data['correlation_id'] === '22222222-2222-4222-8222-222222222222'
                && $data['aggregate_id'] === '33333333-3333-4333-8333-333333333333'
                && $data['payload'] === ['id' => 'agg-1', 'qty' => 2]
                && is_string($data['occurred_on']);
        });
    }

    public function test_publish_throws_when_http_response_is_error(): void
    {
        putenv('EVENTBUS_ENDPOINT=http://eventbus.test/api/event-bus');
        putenv('EVENTBUS_SECRET=secret-token');
        putenv('EVENTBUS_TIMEOUT=5');

        Http::fake([
            '*' => Http::response(['message' => 'boom'], 500),
        ]);

        $bus = new HttpEventBus;

        $this->expectException(\Throwable::class);

        $bus->publish(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCerrada',
            ['id' => 'agg-2'],
            new DateTimeImmutable('2026-04-06T10:00:00+00:00')
        );
    }
}
