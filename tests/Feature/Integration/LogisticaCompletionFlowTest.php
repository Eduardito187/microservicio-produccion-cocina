<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\OrdenEntregaCompletada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @class LogisticaCompletionFlowTest
 * @package Tests\Feature\Integration
 */
class LogisticaCompletionFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_orden_con_n_paquetes_no_emite_hasta_recibir_ultimo_completed(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-n-1',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            [
                'id' => 'it-n-11',
                'op_id' => 'op-n-1',
                'product_id' => null,
                'paquete_id' => 'pkg-n-11',
                'entrega_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'contrato_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-n-12',
                'op_id' => 'op-n-1',
                'product_id' => null,
                'paquete_id' => 'pkg-n-12',
                'entrega_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'contrato_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'it-n-13',
                'op_id' => 'op-n-1',
                'product_id' => null,
                'paquete_id' => 'pkg-n-13',
                'entrega_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'contrato_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
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
                    return ($payload['entregaId'] ?? null) === 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'
                        && ($payload['contratoId'] ?? null) === 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'
                        && ($payload['totalPackages'] ?? null) === 3
                        && ($payload['completedPackages'] ?? null) === 3;
                }),
                'op-n-1'
            );
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $handler->handle([
            'packageId' => 'pkg-n-11',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T16:00:00Z',
        ], ['event_id' => 'evt-n-11']);

        $handler->handle([
            'packageId' => 'pkg-n-12',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T16:05:00Z',
        ], ['event_id' => 'evt-n-12']);

        $orderBeforeLast = DB::table('orden_produccion')->where('id', 'op-n-1')->first();
        $this->assertNotNull($orderBeforeLast);
        $this->assertNull($orderBeforeLast->entrega_completada_at);

        $progressBeforeLast = DB::table('order_delivery_progress')->where('op_id', 'op-n-1')->first();
        $this->assertNotNull($progressBeforeLast);
        $this->assertSame(3, (int) $progressBeforeLast->total_packages);
        $this->assertSame(2, (int) $progressBeforeLast->completed_packages);
        $this->assertSame(1, (int) $progressBeforeLast->pending_packages);

        $handler->handle([
            'packageId' => 'pkg-n-13',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T16:10:00Z',
        ], ['event_id' => 'evt-n-13']);

        $orderAfterLast = DB::table('orden_produccion')->where('id', 'op-n-1')->first();
        $this->assertNotNull($orderAfterLast);
        $this->assertNotNull($orderAfterLast->entrega_completada_at);
    }

    /**
     * @return void
     */
    public function test_evento_duplicado_no_duplica_conteo_ni_emision(): void
    {
        DB::table('orden_produccion')->insert([
            'id' => 'op-n-2',
            'fecha' => '2026-03-02',
            'estado' => 'DESPACHADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'it-n-21',
            'op_id' => 'op-n-2',
            'product_id' => null,
            'paquete_id' => 'pkg-n-21',
            'entrega_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'contrato_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');
        $this->app->instance(DomainEventPublisherInterface::class, $publisher);

        $handler = $this->app->make(LogisticaPaqueteEstadoActualizadoHandler::class);

        $payload = [
            'packageId' => 'pkg-n-21',
            'deliveryStatus' => 'Completed',
            'occurredOn' => '2026-03-02T17:00:00Z',
            'driverId' => '9ddf07e9-be4c-45cc-924a-3b64d84f567b',
        ];

        $handler->handle($payload, ['event_id' => 'evt-n-21']);
        $handler->handle($payload, ['event_id' => 'evt-n-21']);

        $progress = DB::table('order_delivery_progress')->where('op_id', 'op-n-2')->first();
        $this->assertNotNull($progress);
        $this->assertSame(1, (int) $progress->total_packages);
        $this->assertSame(1, (int) $progress->completed_packages);
        $this->assertNotNull($progress->completion_event_id);

        $historyCount = DB::table('package_delivery_history')->where('event_id', 'evt-n-21')->count();
        $this->assertSame(1, $historyCount);
    }
}
