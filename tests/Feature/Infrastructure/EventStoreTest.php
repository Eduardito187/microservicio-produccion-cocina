<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Infrastructure;

use App\Infrastructure\Jobs\PublishOutbox;
use App\Infrastructure\Persistence\Model\Outbox;
use App\Application\Shared\BusInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DateTimeImmutable;

/**
 * @class EventStoreTest
 * @package Tests\Feature\Infrastructure
 */
class EventStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_publish_outbox_persiste_event_store(): void
    {
        Outbox::query()->create([
            'id' => 'ob-1',
            'event_id' => 'evt-100',
            'event_name' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'aggregate_id' => 'op-100',
            'payload' => ['k' => 'v'],
            'occurred_on' => now(),
            'schema_version' => 1,
            'correlation_id' => 'c-100',
        ]);

        $bus = new class implements BusInterface {
            /**
             * @param string $eventId
             * @param string $eventName
             * @param array $payload
             * @param \DateTimeImmutable $occurredOn
             * @param array $meta
             * @return void
             */
            public function publish(string $eventId, string $eventName, array $payload, \DateTimeImmutable $occurredOn, array $meta = []): void
            {
            }
        };

        $job = new PublishOutbox();
        $job->handle($bus);

        $this->assertDatabaseHas('event_store', [
            'event_id' => 'evt-100',
            'event_name' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'aggregate_id' => 'op-100',
            'schema_version' => 1,
            'correlation_id' => 'c-100',
        ]);
    }
}
