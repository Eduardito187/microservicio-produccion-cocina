<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Integration\Handlers\EntregaConfirmadaHandler;
use App\Application\Integration\Handlers\EntregaFallidaHandler;
use App\Application\Integration\Handlers\PaqueteEnRutaHandler;
use App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler;
use App\Domain\Produccion\Events\OrdenEntregaCompletada;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class LogisticaInboundTest
 * @package Tests\Feature\Integration
 */
class LogisticaInboundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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
                'entrega_id' => 'ent-1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-2',
                'op_id' => 'op-1',
                'product_id' => null,
                'paquete_id' => 'pkg-2',
                'entrega_id' => 'ent-1',
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
        ], ['event_id' => 'evt-lg-1']);

        $handler->handle([
            'packageId' => 'pkg-2',
            'deliveryStatus' => 'Failed',
            'occurredOn' => '2026-03-02T10:05:00Z',
        ], ['event_id' => 'evt-lg-2']);

        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-1',
            'delivery_status' => 'confirmada',
        ]);
        $this->assertDatabaseHas('item_despacho', [
            'id' => 'it-2',
            'delivery_status' => 'fallida',
        ]);
        $order = DB::table('orden_produccion')->where('id', 'op-1')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->entrega_completada_at);
    }

    /**
     * @return void
     */
    public function test_logistica_estado_actualizado_emite_completada_cuando_todos_confirmados_e_incluye_entrega_id(): void
    {
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
                'entrega_id' => 'ent-2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-22',
                'op_id' => 'op-2',
                'product_id' => null,
                'paquete_id' => 'pkg-22',
                'entrega_id' => 'ent-2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(function (array $events): bool {
                    if (count($events) !== 1 || !$events[0] instanceof OrdenEntregaCompletada) {
                        return false;
                    }
                    $payload = $events[0]->toArray();
                    return $events[0]->aggregateId() === 'op-2'
                        && ($payload['entregaId'] ?? null) === 'ent-2'
                        && ($payload['confirmedPackages'] ?? null) === 2;
                }),
                'op-2'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-21',
            'deliveryStatus' => 'Delivered',
            'occurredOn' => '2026-03-02T10:00:00Z',
        ], ['event_id' => 'evt-lg-21']);

        $handler->handle([
            'packageId' => 'pkg-22',
            'deliveryStatus' => 'Delivered',
            'occurredOn' => '2026-03-02T10:05:00Z',
        ], ['event_id' => 'evt-lg-22']);

        $order = DB::table('orden_produccion')->where('id', 'op-2')->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->entrega_completada_at);
    }
}
