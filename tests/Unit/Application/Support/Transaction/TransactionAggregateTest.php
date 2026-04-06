<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Support\Transaction;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use PHPUnit\Framework\TestCase;

/**
 * @class TransactionAggregateTest
 */
class TransactionAggregateTest extends TestCase
{
    public function test_run_transaction_delega_en_transaction_manager(): void
    {
        $manager = $this->createMock(TransactionManagerInterface::class);
        $manager->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(\Closure::class))
            ->willReturnCallback(fn (callable $callback) => $callback());

        $aggregate = new TransactionAggregate($manager);
        $result = $aggregate->runTransaction(fn () => 'ok');

        $this->assertSame('ok', $result);
    }

    public function test_after_commit_delega_en_transaction_manager(): void
    {
        $manager = $this->createMock(TransactionManagerInterface::class);
        $manager->expects($this->once())
            ->method('afterCommit')
            ->with($this->isInstanceOf(\Closure::class));

        $aggregate = new TransactionAggregate($manager);
        $aggregate->afterCommit(fn () => null);

        $this->assertTrue(true);
    }
}
