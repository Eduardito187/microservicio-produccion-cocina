<?php

namespace Tests\Feature\EventBus;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'event_id' => 'evt-123',
        ];

        // 1) Sin token => 401
        $this->postJson('/api/event-bus', $payload)->assertStatus(401)->assertJsonPath('message', 'Unauthorized');

        // 2) Con token correcto => ok y crea inbound_event
        $this->withHeader('X-EventBus-Token', 'test-secret')->postJson('/api/event-bus', $payload)
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('inbound_events', ['event_id' => 'evt-123']);

        // 3) Misma request => duplicate, no duplica registro
        $this->withHeader('X-EventBus-Token', 'test-secret')->postJson('/api/event-bus', $payload)
            ->assertOk()->assertJsonPath('status', 'duplicate');
    }
}