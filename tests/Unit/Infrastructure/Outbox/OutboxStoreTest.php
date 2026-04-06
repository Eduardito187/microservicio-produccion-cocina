<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Outbox;

use App\Infrastructure\Persistence\Outbox\OutboxStore;
use App\Infrastructure\Persistence\Outbox\OutboxStoreAdapter;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class OutboxStoreTest
 */
class OutboxStoreTest extends TestCase
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

        Schema::dropIfExists('outbox');
        Schema::create('outbox', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('event_name');
            $table->string('aggregate_id')->nullable();
            $table->unsignedInteger('schema_version')->nullable();
            $table->string('correlation_id')->nullable();
            $table->json('payload');
            $table->dateTime('occurred_on');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_append_generates_uuid_aggregate_and_sets_payload_id_when_input_is_invalid(): void
    {
        putenv('EVENT_SCHEMA_VERSION=3');

        $request = Request::create('/api', 'POST');
        $request->headers->set('X-Correlation-Id', 'corr-header-1');
        $this->app->instance('request', $request);

        OutboxStore::append(
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'not-a-uuid',
            new DateTimeImmutable('2026-04-06T10:00:00+00:00'),
            ['qty' => 2]
        );

        $row = DB::table('outbox')->first();
        $this->assertNotNull($row);
        $this->assertSame(3, (int) $row->schema_version);
        $this->assertSame('corr-header-1', $row->correlation_id);

        $payload = json_decode((string) $row->payload, true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('id', $payload);
        $this->assertSame($row->aggregate_id, $payload['id']);
    }

    public function test_append_preserves_valid_payload_id_and_uses_generated_correlation_if_header_missing(): void
    {
        putenv('EVENT_SCHEMA_VERSION=1');
        putenv('PACT_BYPASS_HEADER_SECRET');

        $request = Request::create('/api', 'POST');
        $this->app->instance('request', $request);

        $aggregateId = '11111111-1111-4111-8111-111111111111';
        $payloadId = '22222222-2222-4222-8222-222222222222';

        OutboxStore::append(
            'App\\Domain\\Produccion\\Events\\OrdenProduccionPlanificada',
            $aggregateId,
            new DateTimeImmutable('2026-04-06T11:00:00+00:00'),
            ['id' => $payloadId, 'items' => 1]
        );

        $row = DB::table('outbox')->first();
        $this->assertNotNull($row);
        $this->assertSame($aggregateId, $row->aggregate_id);
        $this->assertNotNull($row->correlation_id);

        $payload = json_decode((string) $row->payload, true);
        $this->assertSame($payloadId, $payload['id']);
    }

    public function test_outbox_store_adapter_delegates_to_store_append(): void
    {
        $adapter = new OutboxStoreAdapter;

        $adapter->append(
            'App\\Domain\\Produccion\\Events\\OrdenProduccionProcesada',
            '33333333-3333-4333-8333-333333333333',
            new DateTimeImmutable('2026-04-06T12:00:00+00:00'),
            ['id' => '33333333-3333-4333-8333-333333333333']
        );

        $this->assertSame(1, DB::table('outbox')->count());
    }

    public function test_normalize_payload_private_method_covers_both_branches(): void
    {
        $reflection = new ReflectionClass(OutboxStore::class);
        $method = $reflection->getMethod('normalizePayload');
        $method->setAccessible(true);

        $aggregateId = '44444444-4444-4444-8444-444444444444';

        $normalizedA = $method->invoke(null, ['x' => 1], $aggregateId);
        $this->assertSame($aggregateId, $normalizedA['id']);

        $validPayloadId = '55555555-5555-4555-8555-555555555555';
        $normalizedB = $method->invoke(null, ['id' => $validPayloadId, 'x' => 2], $aggregateId);
        $this->assertSame($validPayloadId, $normalizedB['id']);
    }
}
