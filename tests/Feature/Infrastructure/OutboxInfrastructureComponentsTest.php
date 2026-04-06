<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Infrastructure;

use App\Application\Shared\BusInterface;
use App\Application\Shared\OutboxStoreInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Shared\Events\BaseDomainEvent;
use App\Infrastructure\Bus\HttpEventBus;
use App\Infrastructure\Persistence\Outbox\OutboxEventPublisher;
use App\Infrastructure\Persistence\Outbox\OutboxUnitOfWork;
use App\Infrastructure\Persistence\Transaction\TransactionManager;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @class OutboxInfrastructureComponentsTest
 */
class OutboxInfrastructureComponentsTest extends TestCase
{
    public function test_http_event_bus_publica_payload_con_headers_y_meta(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        config([
            'app.env' => 'testing',
        ]);
        putenv('EVENTBUS_ENDPOINT=http://example.test/events');
        putenv('EVENTBUS_SECRET=secret-token');
        putenv('EVENTBUS_TIMEOUT=5');

        $bus = new HttpEventBus;
        $bus->publish(
            'evt-1',
            'App\\Domain\\Event',
            ['x' => 1],
            new DateTimeImmutable('2026-02-01T10:00:00Z'),
            ['schema_version' => 2, 'correlation_id' => 'corr-1', 'aggregate_id' => 'agg-1']
        );

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'http://example.test/events'
                && $request->hasHeader('X-EventBus-Token', 'secret-token')
                && $data['event_id'] === 'evt-1'
                && $data['event'] === 'App\\Domain\\Event'
                && $data['schema_version'] === 2
                && $data['correlation_id'] === 'corr-1'
                && $data['aggregate_id'] === 'agg-1'
                && $data['payload'] === ['x' => 1];
        });
    }

    public function test_outbox_unit_of_work_register_y_flush(): void
    {
        $store = new class implements OutboxStoreInterface
        {
            public array $rows = [];

            public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
            {
                $this->rows[] = compact('name', 'aggregateId', 'payload') + ['occurredOn' => $occurredOn->format(DATE_ATOM)];
            }
        };

        $uow = new OutboxUnitOfWork($store);
        $event = new BaseDomainEvent('agg-1', new DateTimeImmutable('2026-03-01T10:00:00Z'));

        $uow->register([$event], null);
        $uow->flush();

        $this->assertCount(1, $store->rows);
        $this->assertSame(BaseDomainEvent::class, $store->rows[0]['name']);
        $this->assertSame('agg-1', $store->rows[0]['aggregateId']);
    }

    public function test_outbox_unit_of_work_flush_vacio_no_hace_nada(): void
    {
        $store = new class implements OutboxStoreInterface
        {
            public int $appends = 0;

            public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
            {
                $this->appends++;
            }
        };

        $uow = new OutboxUnitOfWork($store);
        $uow->flush();

        $this->assertSame(0, $store->appends);
    }

    public function test_outbox_event_publisher_registra_eventos_si_hay_contenido(): void
    {
        $uow = $this->createMock(OutboxStoreInterface::class);
        $uowAdapter = new OutboxUnitOfWork($uow);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $publisher = new OutboxEventPublisher($uowAdapter, $tx);
        $event = new BaseDomainEvent('agg-2');

        $uow->expects($this->once())->method('append');

        $publisher->publish([$event], 'agg-2');
        $uowAdapter->flush();
    }

    public function test_outbox_event_publisher_no_registra_si_eventos_vacio(): void
    {
        $uow = $this->createMock(OutboxStoreInterface::class);
        $uowAdapter = new OutboxUnitOfWork($uow);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $publisher = new OutboxEventPublisher($uowAdapter, $tx);

        $uow->expects($this->never())->method('append');
        $publisher->publish([], 'agg-3');
        $uowAdapter->flush();
    }

    public function test_transaction_manager_run_hace_clear_flush_clear_y_retorna_resultado(): void
    {
        $uow = $this->createMock(\App\Application\Shared\OutboxUnitOfWorkInterface::class);
        $manager = new TransactionManager($uow);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $uow->expects($this->exactly(2))->method('clear');
        $uow->expects($this->once())->method('flush');

        $result = $manager->run(fn () => 'ok-transaction');
        $this->assertSame('ok-transaction', $result);
    }

    public function test_transaction_manager_after_commit_delega_a_db(): void
    {
        $uow = $this->createMock(\App\Application\Shared\OutboxUnitOfWorkInterface::class);
        $manager = new TransactionManager($uow);

        DB::shouldReceive('afterCommit')->once();
        $manager->afterCommit(fn () => null);

        $this->assertTrue(true);
    }

    public function test_publish_outbox_job_cuando_no_hay_pendientes_no_publica_en_bus(): void
    {
        $drivers = \PDO::getAvailableDrivers();
        if (! in_array('mysql', $drivers, true) && ! in_array('sqlite', $drivers, true)) {
            $this->markTestSkipped('No PDO mysql/sqlite driver available for outbox DB test.');
        }

        $bus = new class implements BusInterface
        {
            public int $calls = 0;

            public function publish(string $eventId, string $eventName, array $payload, DateTimeImmutable $occurredOn, array $meta = []): void
            {
                $this->calls++;
            }
        };

        // Limpia por si otro test dejó registros.
        DB::table('outbox')->delete();

        $job = new \App\Infrastructure\Jobs\PublishOutbox;
        $job->handle($bus);

        $this->assertSame(0, $bus->calls);
    }
}
