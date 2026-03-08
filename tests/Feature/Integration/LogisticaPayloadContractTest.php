<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @class LogisticaPayloadContractTest
 */
class LogisticaPayloadContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_completed_se_mapea_a_confirmada(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-c-1',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-c-11',
                'op_id' => 'op-c-1',
                'product_id' => null,
                'paquete_id' => 'pkg-c-11',
                'entrega_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
                'contrato_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-c-12',
                'op_id' => 'op-c-1',
                'product_id' => null,
                'paquete_id' => 'pkg-c-12',
                'entrega_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
                'contrato_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);
        $handler->handle([
            'packageId' => 'pkg-c-11',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T18:00:00Z',
        ], ['event_id' => 'evt-c-11']);

        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-c-11',
            'delivery_status' => 'confirmada',
        ]);
    }

    public function test_contract_evidencia_string_y_objeto_se_parsean_correctamente(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-c-2',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-c-21',
                'op_id' => 'op-c-2',
                'product_id' => null,
                'paquete_id' => 'pkg-c-21',
                'entrega_id' => '10101010-1010-4010-8010-101010101010',
                'contrato_id' => '20202020-2020-4020-8020-202020202020',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-c-22',
                'op_id' => 'op-c-2',
                'product_id' => null,
                'paquete_id' => 'pkg-c-22',
                'entrega_id' => '10101010-1010-4010-8010-101010101010',
                'contrato_id' => '20202020-2020-4020-8020-202020202020',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-c-21',
            'deliveryStatus' => 'InTransit',
            'deliveryEvidence' => 'evidencia-texto.jpg',
            'occurredOn' => '2026-03-02T18:10:00Z',
        ], ['event_id' => 'evt-c-21']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-c-21',
            'paquete_id' => 'pkg-c-21',
            'foto_url' => 'evidencia-texto.jpg',
        ]);

        $handler->handle([
            'packageId' => 'pkg-c-22',
            'deliveryStatus' => 'InTransit',
            'deliveryEvidence' => [
                'url' => 'https://cdn.local/evidencia-obj.jpg',
                'geo' => ['lat' => -17.8, 'lng' => -63.1],
            ],
            'occurredOn' => '2026-03-02T18:12:00Z',
        ], ['event_id' => 'evt-c-22']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-c-22',
            'paquete_id' => 'pkg-c-22',
            'foto_url' => 'https://cdn.local/evidencia-obj.jpg',
        ]);

        $record = DB::table('entrega_evidencia')->where('event_id', 'evt-c-22')->first();
        $this->assertNotNull($record);
        $this->assertIsString($record->geo);
        $this->assertStringContainsString('lat', $record->geo);
        $this->assertStringContainsString('lng', $record->geo);
    }

    public function test_contract_driver_id_valido_se_guarda_e_invalido_se_ignora(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-c-3',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-c-31',
                'op_id' => 'op-c-3',
                'product_id' => null,
                'paquete_id' => 'pkg-c-31',
                'entrega_id' => '30303030-3030-4030-8030-303030303030',
                'contrato_id' => '40404040-4040-4040-8040-404040404040',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-c-32',
                'op_id' => 'op-c-3',
                'product_id' => null,
                'paquete_id' => 'pkg-c-32',
                'entrega_id' => '30303030-3030-4030-8030-303030303030',
                'contrato_id' => '40404040-4040-4040-8040-404040404040',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-c-31',
            'deliveryStatus' => 'InTransit',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'occurredOn' => '2026-03-02T18:20:00Z',
        ], ['event_id' => 'evt-c-31']);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-c-31',
            'driver_id' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
        ]);

        $handler->handle([
            'packageId' => 'pkg-c-32',
            'deliveryStatus' => 'InTransit',
            'driverId' => 'driver-invalido',
            'occurredOn' => '2026-03-02T18:22:00Z',
        ], ['event_id' => 'evt-c-32']);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-c-32',
            'driver_id' => null,
        ]);
    }
}
