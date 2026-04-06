<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure;

use App\Application\Shared\OutboxStoreInterface;
use App\Domain\Shared\Events\BaseDomainEvent;
use App\Infrastructure\Persistence\Outbox\OutboxUnitOfWork;
use App\Infrastructure\Persistence\Transaction\TransactionManager;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @class OutboxTransactionUnitTest
 */
class OutboxTransactionUnitTest extends TestCase
{
    public function test_outbox_unit_of_work_register_flush_and_clear(): void
    {
        $store = new class implements OutboxStoreInterface
        {
            public array $rows = [];

            public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
            {
                $this->rows[] = [
                    'name' => $name,
                    'aggregateId' => $aggregateId,
                    'occurredOn' => $occurredOn,
                    'payload' => $payload,
                ];
            }
        };

        $uow = new OutboxUnitOfWork($store);
        $eventA = new BaseDomainEvent('agg-1', new DateTimeImmutable('2026-01-01T00:00:00Z'));
        $eventB = new BaseDomainEvent('agg-2', new DateTimeImmutable('2026-01-01T01:00:00Z'));

        $uow->register([$eventA], null);
        $uow->register([$eventB], 'forced-aggregate');
        $uow->flush();

        $this->assertCount(2, $store->rows);
        $this->assertSame('agg-1', $store->rows[0]['aggregateId']);
        $this->assertSame('forced-aggregate', $store->rows[1]['aggregateId']);

        $uow->clear();
        $uow->flush();
        $this->assertCount(2, $store->rows);
    }

    public function test_transaction_manager_run_flushes_and_clears_on_success_and_exception(): void
    {
        $uow = $this->createMock(\App\Application\Shared\OutboxUnitOfWorkInterface::class);
        $manager = new TransactionManager($uow);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $uow->expects($this->exactly(2))->method('clear');
        $uow->expects($this->once())->method('flush');

        $this->assertSame('ok', $manager->run(fn () => 'ok'));

        $uow2 = $this->createMock(\App\Application\Shared\OutboxUnitOfWorkInterface::class);
        $manager2 = new TransactionManager($uow2);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $uow2->expects($this->exactly(2))->method('clear');
        $uow2->expects($this->never())->method('flush');

        $this->expectException(\RuntimeException::class);
        $manager2->run(function () {
            throw new \RuntimeException('boom');
        });
    }

    public function test_transaction_manager_after_commit_delegates_to_db_facade(): void
    {
        $uow = $this->createMock(\App\Application\Shared\OutboxUnitOfWorkInterface::class);
        $manager = new TransactionManager($uow);

        DB::shouldReceive('afterCommit')->once();
        $manager->afterCommit(fn () => null);

        $this->assertTrue(true);
    }
}
