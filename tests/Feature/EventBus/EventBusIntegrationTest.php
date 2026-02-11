<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\EventBus;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class EventBusIntegrationTest
 * @package Tests\Feature\EventBus
 */
class EventBusIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_event_bus_rechaza_sin_token_y_acepta_con_token_y_es_idempotente(): void
    {
        $_ENV['EVENTBUS_SECRET'] = 'test-secret';

        $payload = [
            'event' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'occurred_on' => '2025-11-04T10:00:00Z',
            'payload' => ['ordenProduccionId' => 1],
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'schema_version' => 1,
            'correlation_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
        ];

        // 1) Sin token => 401
        $this->postJson('/api/event-bus', $payload)->assertStatus(401)->assertJsonPath('message', 'Unauthorized');

        // 2) Con token correcto => ok y crea inbound_event
        $this->withHeader('X-EventBus-Token', 'test-secret')->postJson('/api/event-bus', $payload)
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('inbound_events', [
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'schema_version' => 1,
            'correlation_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
        ]);

        // 3) Misma request => duplicate, no duplica registro
        $this->withHeader('X-EventBus-Token', 'test-secret')->postJson('/api/event-bus', $payload)
            ->assertOk()->assertJsonPath('status', 'duplicate');
    }

    /**
     * @return void
     */
    public function test_event_bus_rechaza_schema_version_no_soportado(): void
    {
        $_ENV['EVENTBUS_SECRET'] = 'test-secret';
        config(['rabbitmq.inbound.schema_versions' => '1']);

        $payload = [
            'event' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'occurred_on' => '2025-11-04T10:00:00Z',
            'payload' => ['ordenProduccionId' => 1],
            'event_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'schema_version' => 9,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
        ];

        $this->withHeader('X-EventBus-Token', 'test-secret')->postJson('/api/event-bus', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Unsupported schema_version: 9');
    }
}
