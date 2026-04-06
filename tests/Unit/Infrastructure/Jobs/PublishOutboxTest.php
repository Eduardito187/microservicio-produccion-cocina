<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Jobs;

use App\Application\Shared\BusInterface;
use App\Infrastructure\Jobs\PublishOutbox;
use App\Infrastructure\Persistence\Model\Outbox;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class PublishOutboxTest
 */
class PublishOutboxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createOutboxTables();
    }

    public function test_handle_returns_without_publishing_when_outbox_is_empty(): void
    {
        $bus = new class implements BusInterface
        {
            public int $calls = 0;

            public function publish(string $eventId, string $eventName, array $payload, DateTimeImmutable $occurredOn, array $meta = []): void
            {
                $this->calls++;
            }
        };

        $job = new PublishOutbox;
        $job->handle($bus);

        $this->assertSame(0, $bus->calls);
    }

    public function test_handle_publishes_pending_event_and_marks_outbox_as_published(): void
    {
        $eventId = (string) Str::uuid();

        Outbox::query()->create([
            'id' => (string) Str::uuid(),
            'event_id' => $eventId,
            'event_name' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'aggregate_id' => 'agg-1',
            'payload' => ['k' => 'v'],
            'occurred_on' => now(),
            'schema_version' => 1,
            'correlation_id' => (string) Str::uuid(),
        ]);

        $published = [];
        $bus = new class($published) implements BusInterface
        {
            private array $published;

            public function __construct(array &$published)
            {
                $this->published = &$published;
            }

            public function publish(string $eventId, string $eventName, array $payload, DateTimeImmutable $occurredOn, array $meta = []): void
            {
                $this->published[] = compact('eventId', 'eventName', 'payload', 'meta');
            }
        };

        $job = new PublishOutbox;
        $job->handle($bus);

        $this->assertCount(1, $published);
        $this->assertSame($eventId, $published[0]['eventId']);

        $row = Outbox::query()->where('event_id', $eventId)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row?->published_at);
        $this->assertNull($row?->locked_at);
        $this->assertNull($row?->locked_by);

        $this->assertDatabaseHas('event_store', [
            'event_id' => $eventId,
            'event_name' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
        ]);
    }

    private function createOutboxTables(): void
    {
        Schema::dropIfExists('event_store');
        Schema::dropIfExists('outbox');

        Schema::create('outbox', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('event_name');
            $table->string('aggregate_id')->nullable();
            $table->json('payload');
            $table->timestamp('occurred_on');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            $table->unsignedInteger('schema_version')->nullable();
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });

        Schema::create('event_store', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('event_name');
            $table->string('aggregate_id')->nullable();
            $table->json('payload');
            $table->timestamp('occurred_on');
            $table->unsignedInteger('schema_version')->nullable();
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });
    }
}
