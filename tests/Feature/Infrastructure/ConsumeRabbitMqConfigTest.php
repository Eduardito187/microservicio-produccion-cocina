<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Infrastructure;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class ConsumeRabbitMqConfigTest
 * @package Tests\Feature\Infrastructure
 */
class ConsumeRabbitMqConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_consumer_falla_si_inbound_no_esta_configurado(): void
    {
        config([
            'rabbitmq.inbound.exchange' => '',
            'rabbitmq.inbound.queue' => '',
            'rabbitmq.inbound.routing_keys' => '',
        ]);

        $this->artisan('rabbitmq:consume --once')
            ->expectsOutput('INBOUND_RABBITMQ_QUEUE is required for inbound consumer.')
            ->assertExitCode(1);
    }

    /**
     * @return void
     */
    public function test_consumer_falla_si_falta_routing_keys(): void
    {
        config([
            'rabbitmq.inbound.exchange' => 'inbound.events',
            'rabbitmq.inbound.queue' => 'inbound.queue',
            'rabbitmq.inbound.routing_keys' => '',
        ]);

        $this->artisan('rabbitmq:consume --once')
            ->expectsOutput('INBOUND_RABBITMQ_ROUTING_KEYS is required for inbound consumer.')
            ->assertExitCode(1);
    }

    /**
     * @return void
     */
    public function test_consumer_falla_si_inbound_apunta_a_outbox(): void
    {
        config([
            'rabbitmq.exchange' => 'outbox.events',
            'rabbitmq.queue' => 'produccion.outbox',
            'rabbitmq.inbound.exchange' => 'outbox.events',
            'rabbitmq.inbound.queue' => 'produccion.outbox',
            'rabbitmq.inbound.routing_keys' => 'produccion.*',
        ]);

        $this->artisan('rabbitmq:consume --once')
            ->expectsOutput('Inbound configuration must not match outbox exchange/queue.')
            ->assertExitCode(1);
    }
}
