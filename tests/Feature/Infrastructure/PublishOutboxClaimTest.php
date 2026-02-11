<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Infrastructure;

use App\Application\Shared\BusInterface;
use App\Infrastructure\Jobs\PublishOutbox;
use App\Infrastructure\Persistence\Model\Outbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use DateTimeImmutable;

/**
 * @class PublishOutboxClaimTest
 * @package Tests\Feature\Infrastructure
 */
class PublishOutboxClaimTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_publish_outbox_does_not_publish_twice(): void
    {
        $published = [];

        $this->app->instance(BusInterface::class, new class($published) implements BusInterface {
            /**
             * @var array
             */
            private array $published;

            /**
             * Constructor
             *
             * @param array & $published
             */
            public function __construct(array &$published)
            {
                $this->published = &$published;
            }

            /**
             * @param string $eventId
             * @param string $name
             * @param array $payload
             * @param DateTimeImmutable $occurredOn
             * @return void
             */
            public function publish(string $eventId, string $name, array $payload, DateTimeImmutable $occurredOn): void
            {
                $this->published[] = $eventId;
            }
        });

        $eventId = (string) Str::uuid();
        Outbox::create([
            'event_id' => $eventId,
            'event_name' => 'TestEvent',
            'aggregate_id' => (string) Str::uuid(),
            'payload' => ['x' => 1],
            'occurred_on' => now(),
        ]);

        $job = new PublishOutbox();
        $job->handle($this->app->make(BusInterface::class));
        $job->handle($this->app->make(BusInterface::class));

        $this->assertCount(1, $published);
        $this->assertSame($eventId, $published[0]);

        $row = Outbox::where('event_id', $eventId)->firstOrFail();
        $this->assertNotNull($row->published_at);
    }
}
