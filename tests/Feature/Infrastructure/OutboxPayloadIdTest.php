<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Infrastructure;

use App\Infrastructure\Persistence\Model\Outbox;
use App\Infrastructure\Persistence\Outbox\OutboxStore;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class OutboxPayloadIdTest
 */
class OutboxPayloadIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_append_completa_payload_id_con_aggregate_uuid_si_falta(): void
    {
        $aggregateId = (string) Str::uuid();

        OutboxStore::append(
            'TestEvent',
            $aggregateId,
            new DateTimeImmutable('2026-03-02T00:00:00Z'),
            ['foo' => 'bar']
        );

        $row = Outbox::query()->latest('created_at')->firstOrFail();
        $this->assertSame($aggregateId, $row->aggregate_id);
        $this->assertIsArray($row->payload);
        $this->assertSame($aggregateId, $row->payload['id'] ?? null);
        $this->assertTrue(Str::isUuid($row->payload['id'] ?? null));
    }

    public function test_append_reemplaza_payload_id_vacio_por_uuid_valido(): void
    {
        $aggregateId = (string) Str::uuid();

        OutboxStore::append(
            'TestEvent',
            $aggregateId,
            new DateTimeImmutable('2026-03-02T00:00:00Z'),
            ['id' => '']
        );

        $row = Outbox::query()->latest('created_at')->firstOrFail();
        $this->assertIsArray($row->payload);
        $this->assertSame($aggregateId, $row->payload['id'] ?? null);
        $this->assertTrue(Str::isUuid($row->payload['id'] ?? null));
    }
}
