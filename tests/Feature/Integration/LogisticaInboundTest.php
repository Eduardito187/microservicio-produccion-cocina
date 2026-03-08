<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Integration\Handlers\EntregaConfirmadaHandler;
use App\Application\Integration\Handlers\EntregaFallidaHandler;
use App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler;
use App\Application\Integration\Handlers\PaqueteEnRutaHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\EntregaInconsistenciaDetectada;
use App\Domain\Produccion\Events\PaqueteEntregado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @class LogisticaInboundTest
 */
class LogisticaInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_entrega_confirmada_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(EntregaConfirmadaHandler::class);

        $payload = [
            'paqueteId' => 'paq-10',
            'fotoUrl' => 'http://example.com/foto.jpg',
            'geo' => ['lat' => '1.0', 'lng' => '2.0'],
            'occurredOn' => '2025-10-10T10:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-10']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-10',
            'paquete_id' => 'paq-10',
            'status' => 'confirmada',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'entrega_confirmada',
            'value' => 1,
        ]);
    }

    public function test_entrega_fallida_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(EntregaFallidaHandler::class);

        $payload = [
            'paqueteId' => 'paq-20',
            'motivo' => 'no atencion',
            'occurredOn' => '2025-10-10T11:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-20']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-20',
            'paquete_id' => 'paq-20',
            'status' => 'fallida',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'entrega_fallida',
            'value' => 1,
        ]);
    }

    public function test_paquete_en_ruta_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(PaqueteEnRutaHandler::class);

        $payload = [
            'paqueteId' => 'paq-30',
            'rutaId' => 'ruta-1',
            'occurredOn' => '2025-10-10T12:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-30']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-30',
            'paquete_id' => 'paq-30',
            'status' => 'en_ruta',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'paquete_en_ruta',
            'value' => 1,
        ]);
    }

    public function test_logistica_estado_actualizado_no_emite_completada_si_hay_paquete_fallido(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-1',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-1',
                'op_id' => 'op-1',
                'product_id' => null,
                'paquete_id' => 'pkg-1',
                'entrega_id' => '11111111-1111-1111-1111-111111111111',
                'contrato_id' => '22222222-2222-2222-2222-222222222222',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-2',
                'op_id' => 'op-1',
                'product_id' => null,
                'paquete_id' => 'pkg-2',
                'entrega_id' => '11111111-1111-1111-1111-111111111111',
                'contrato_id' => '22222222-2222-2222-2222-222222222222',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-1',
            'deliveryStatus' => 'Delivered',
            'occurredOn' => '2026-03-02T10:00:00Z',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
        ], ['event_id' => 'evt-lg-1']);

        $handler->handle([
            'packageId' => 'pkg-2',
            'deliveryStatus' => 'Failed',
            'occurredOn' => '2026-03-02T10:05:00Z',
        ], ['event_id' => 'evt-lg-2']);

        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-1',
            'delivery_status' => 'confirmada',
            'driver_id' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
        ]);
        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-2',
            'delivery_status' => 'fallida',
        ]);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-1',
            'status' => 'confirmada',
            'status_locked' => 1,
        ]);

        $this->assertDatabaseHas('order_delivery_progress', [
            'op_id' => 'op-1',
            'total_packages' => 2,
            'completed_packages' => 1,
            'pending_packages' => 1,
            'entrega_id' => '11111111-1111-1111-1111-111111111111',
            'contrato_id' => '22222222-2222-2222-2222-222222222222',
        ]);

        $order = DB::table('orden_produccion')->where('id', 'op-1')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->entrega_completada_at);
    }

    public function test_logistica_estado_actualizado_emite_completada_cuando_todos_confirmados_e_incluye_entrega_y_contrato(): void
    {
        DB::table('calendario')->insert([
            'id' => 'cal-2',
            'fecha' => '2026-03-02',
            'entrega_id' => '33333333-3333-3333-3333-333333333333',
            'contrato_id' => '44444444-4444-4444-4444-444444444444',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orden_produccion')->insert([
            'id' => 'op-2',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-21',
                'op_id' => 'op-2',
                'product_id' => null,
                'paquete_id' => 'pkg-21',
                'entrega_id' => '33333333-3333-3333-3333-333333333333',
                'contrato_id' => '44444444-4444-4444-4444-444444444444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-22',
                'op_id' => 'op-2',
                'product_id' => null,
                'paquete_id' => 'pkg-22',
                'entrega_id' => '33333333-3333-3333-3333-333333333333',
                'contrato_id' => '44444444-4444-4444-4444-444444444444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('calendario_item')->insert([
            [
                'id' => 'ci-21',
                'calendario_id' => 'cal-2',
                'item_despacho_id' => 'it-21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ci-22',
                'calendario_id' => 'cal-2',
                'item_despacho_id' => 'it-22',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    if (count($events) !== 1 || ! $events[0] instanceof PaqueteEntregado) {
                        return false;
                    }
                    $payload = $events[0]->toArray();

                    return $events[0]->aggregateId() === 'op-2'
                        && ($payload['calendarioId'] ?? null) === 'cal-2'
                        && ($payload['contratoId'] ?? null) === '44444444-4444-4444-4444-444444444444'
                        && ($payload['estado'] ?? null) === 'entregado';
                }),
                'op-2'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-21',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T10:00:00Z',
        ], ['event_id' => 'evt-lg-21']);

        $handler->handle([
            'packageId' => 'pkg-22',
            'deliveryStatus' => 'Delivered',
            'occurredOn' => '2026-03-02T10:05:00Z',
        ], ['event_id' => 'evt-lg-22']);

        $this->assertDatabaseHas('order_delivery_progress', [
            'op_id' => 'op-2',
            'total_packages' => 2,
            'completed_packages' => 2,
            'pending_packages' => 0,
            'entrega_id' => '33333333-3333-3333-3333-333333333333',
            'contrato_id' => '44444444-4444-4444-4444-444444444444',
        ]);

        $this->assertDatabaseHas('package_delivery_history', [
            'event_id' => 'evt-lg-22',
            'package_id' => 'pkg-22',
            'received_status' => 'delivered',
        ]);

        $order = DB::table('orden_produccion')->where('id', 'op-2')->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->entrega_completada_at);
    }

    public function test_logistica_estado_actualizado_bloquea_cambios_despues_de_completed(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-3',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'it-31',
            'op_id' => 'op-3',
            'product_id' => null,
            'paquete_id' => 'pkg-31',
            'entrega_id' => '55555555-5555-5555-5555-555555555555',
            'contrato_id' => '66666666-6666-6666-6666-666666666666',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-31',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T10:00:00Z',
        ], ['event_id' => 'evt-lg-31']);

        $handler->handle([
            'packageId' => 'pkg-31',
            'deliveryStatus' => 'Failed',
            'occurredOn' => '2026-03-02T10:10:00Z',
        ], ['event_id' => 'evt-lg-32']);

        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-31',
            'delivery_status' => 'confirmada',
        ]);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-31',
            'status' => 'confirmada',
            'status_locked' => 1,
            'last_event_id' => 'evt-lg-32',
        ]);
    }

    public function test_logistica_estado_actualizado_no_reemite_evento_cuando_completion_event_id_ya_existe(): void
    {
        DB::table('calendario')->insert([
            'id' => 'cal-4',
            'fecha' => '2026-03-02',
            'entrega_id' => '77777777-7777-7777-7777-777777777777',
            'contrato_id' => '88888888-8888-8888-8888-888888888888',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orden_produccion')->insert([
            'id' => 'op-4',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-41',
                'op_id' => 'op-4',
                'product_id' => null,
                'paquete_id' => 'pkg-41',
                'entrega_id' => '77777777-7777-7777-7777-777777777777',
                'contrato_id' => '88888888-8888-8888-8888-888888888888',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-42',
                'op_id' => 'op-4',
                'product_id' => null,
                'paquete_id' => 'pkg-42',
                'entrega_id' => '77777777-7777-7777-7777-777777777777',
                'contrato_id' => '88888888-8888-8888-8888-888888888888',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('calendario_item')->insert([
            [
                'id' => 'ci-41',
                'calendario_id' => 'cal-4',
                'item_despacho_id' => 'it-41',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ci-42',
                'calendario_id' => 'cal-4',
                'item_despacho_id' => 'it-42',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    if (count($events) !== 1 || ! $events[0] instanceof PaqueteEntregado) {
                        return false;
                    }
                    $payload = $events[0]->toArray();

                    return ($payload['estado'] ?? null) === 'entregado'
                        && ($payload['calendarioId'] ?? null) === 'cal-4'
                        && ($payload['contratoId'] ?? null) === '88888888-8888-8888-8888-888888888888';
                }),
                'op-4'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-41',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T11:00:00Z',
        ], ['event_id' => 'evt-lg-41']);

        $handler->handle([
            'packageId' => 'pkg-42',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T11:05:00Z',
        ], ['event_id' => 'evt-lg-42']);

        // Reintento posterior: no debe reemitir porque completion_event_id ya fue marcado.
        $handler->handle([
            'packageId' => 'pkg-41',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T11:10:00Z',
        ], ['event_id' => 'evt-lg-43']);

        $this->assertDatabaseHas('order_delivery_progress', [
            'op_id' => 'op-4',
            'total_packages' => 2,
            'completed_packages' => 2,
            'pending_packages' => 0,
        ]);

        $progress = DB::table('order_delivery_progress')->where('op_id', 'op-4')->first();
        $this->assertNotNull($progress);
        $this->assertNotNull($progress->completion_event_id);
    }

    public function test_logistica_estado_actualizado_guarda_driver_en_tracking_y_history(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-5',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'it-51',
            'op_id' => 'op-5',
            'product_id' => null,
            'paquete_id' => 'pkg-51',
            'entrega_id' => '99999999-9999-9999-9999-999999999999',
            'contrato_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-51',
            'deliveryStatus' => 'InTransit',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'occurredOn' => '2026-03-02T12:00:00Z',
        ], ['event_id' => 'evt-lg-51']);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-51',
            'driver_id' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'status' => 'en_ruta',
        ]);

        $this->assertDatabaseHas('package_delivery_history', [
            'event_id' => 'evt-lg-51',
            'package_id' => 'pkg-51',
            'driver_id' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'received_status' => 'intransit',
        ]);
    }

    public function test_logistica_estado_actualizado_mantiene_historial_y_actualiza_driver_actual(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-6',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-61',
                'op_id' => 'op-6',
                'product_id' => null,
                'paquete_id' => 'pkg-61',
                'entrega_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'contrato_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-62',
                'op_id' => 'op-6',
                'product_id' => null,
                'paquete_id' => 'pkg-62',
                'entrega_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'contrato_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->never())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-61',
            'deliveryStatus' => 'InTransit',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'occurredOn' => '2026-03-02T13:00:00Z',
        ], ['event_id' => 'evt-lg-61']);

        $handler->handle([
            'packageId' => 'pkg-61',
            'deliveryStatus' => 'Failed',
            'driverId' => '11111111-2222-4333-8444-555555555555',
            'occurredOn' => '2026-03-02T13:05:00Z',
        ], ['event_id' => 'evt-lg-62']);

        $this->assertDatabaseHas('package_delivery_tracking', [
            'package_id' => 'pkg-61',
            'driver_id' => '11111111-2222-4333-8444-555555555555',
            'status' => 'fallida',
        ]);

        $this->assertDatabaseHas('package_delivery_history', [
            'event_id' => 'evt-lg-61',
            'package_id' => 'pkg-61',
            'driver_id' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'received_status' => 'intransit',
        ]);

        $this->assertDatabaseHas('package_delivery_history', [
            'event_id' => 'evt-lg-62',
            'package_id' => 'pkg-61',
            'driver_id' => '11111111-2222-4333-8444-555555555555',
            'received_status' => 'failed',
        ]);
    }

    public function test_logistica_estado_actualizado_encola_inconsistencia_si_falta_entrega_y_contrato(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-7',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'it-71',
            'op_id' => 'op-7',
            'product_id' => null,
            'paquete_id' => 'pkg-71',
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    return count($events) === 1 && $events[0] instanceof EntregaInconsistenciaDetectada;
                }),
                'op-7'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-71',
            'deliveryStatus' => 'Completed',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'occurredOn' => '2026-03-02T14:00:00Z',
        ], ['event_id' => 'evt-lg-71']);

        $this->assertDatabaseHas('delivery_inconsistency_queue', [
            'event_id' => 'evt-lg-71',
            'op_id' => 'op-7',
            'reason' => 'missing_delivery_context_for_consolidated_event',
        ]);

        $progress = DB::table('order_delivery_progress')->where('op_id', 'op-7')->first();
        if ($progress !== null) {
            $this->assertNull($progress->completion_event_id);
        }

        $order = DB::table('orden_produccion')->where('id', 'op-7')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->entrega_completada_at);
    }

    public function test_logistica_estado_actualizado_deduplica_inconsistencia_por_evento_y_razon(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-8',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'it-81',
            'op_id' => 'op-8',
            'product_id' => null,
            'paquete_id' => 'pkg-81',
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    return count($events) === 1 && $events[0] instanceof EntregaInconsistenciaDetectada;
                }),
                'op-8'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $payload = [
            'packageId' => 'pkg-81',
            'deliveryStatus' => 'Completed',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
            'occurredOn' => '2026-03-02T15:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-lg-81']);
        $handler->handle($payload, ['event_id' => 'evt-lg-81']);

        $count = DB::table('delivery_inconsistency_queue')
            ->where('event_id', 'evt-lg-81')
            ->where('reason', 'missing_delivery_context_for_consolidated_event')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_logistica_estado_actualizado_emite_consolidado_no_entregado_cuando_todos_fallidos(): void
    {
        DB::table('calendario')->insert([
            'id' => 'cal-9',
            'fecha' => '2026-03-02',
            'entrega_id' => '12121212-1212-1212-1212-121212121212',
            'contrato_id' => '34343434-3434-3434-3434-343434343434',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orden_produccion')->insert([
            'id' => 'op-9',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-91',
                'op_id' => 'op-9',
                'product_id' => null,
                'paquete_id' => 'pkg-91',
                'entrega_id' => '12121212-1212-1212-1212-121212121212',
                'contrato_id' => '34343434-3434-3434-3434-343434343434',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-92',
                'op_id' => 'op-9',
                'product_id' => null,
                'paquete_id' => 'pkg-92',
                'entrega_id' => '12121212-1212-1212-1212-121212121212',
                'contrato_id' => '34343434-3434-3434-3434-343434343434',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('calendario_item')->insert([
            [
                'id' => 'ci-91',
                'calendario_id' => 'cal-9',
                'item_despacho_id' => 'it-91',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ci-92',
                'calendario_id' => 'cal-9',
                'item_despacho_id' => 'it-92',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    if (count($events) !== 1 || ! $events[0] instanceof PaqueteEntregado) {
                        return false;
                    }
                    $payload = $events[0]->toArray();

                    return ($payload['ordenProduccionId'] ?? null) === 'op-9'
                        && ($payload['calendarioId'] ?? null) === 'cal-9'
                        && ($payload['contratoId'] ?? null) === '34343434-3434-3434-3434-343434343434'
                        && ($payload['estado'] ?? null) === 'no entregado';
                }),
                'op-9'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-91',
            'deliveryStatus' => 'Failed',
            'occurredOn' => '2026-03-02T16:00:00Z',
        ], ['event_id' => 'evt-lg-91']);

        $handler->handle([
            'packageId' => 'pkg-92',
            'deliveryStatus' => 'Fallido',
            'occurredOn' => '2026-03-02T16:05:00Z',
        ], ['event_id' => 'evt-lg-92']);

        $progress = DB::table('order_delivery_progress')->where('op_id', 'op-9')->first();
        $this->assertNotNull($progress);
        $this->assertNotNull($progress->completion_event_id);
    }
}
