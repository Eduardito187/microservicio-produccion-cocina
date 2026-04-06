<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure;

use App\Application\Shared\OutboxUnitOfWorkInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Shared\Events\BaseDomainEvent;
use App\Infrastructure\Persistence\Outbox\OutboxEventPublisher;
use Tests\TestCase;

/**
 * @class OutboxEventPublisherInfrastructureTest
 */
class OutboxEventPublisherInfrastructureTest extends TestCase
{
    public function test_publish_registers_events_when_non_empty(): void
    {
        $uow = $this->createMock(OutboxUnitOfWorkInterface::class);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $event = new BaseDomainEvent('agg-1');

        $uow->expects($this->once())
            ->method('register')
            ->with([$event], 'agg-1');

        $publisher = new OutboxEventPublisher($uow, $tx);
        $publisher->publish([$event], 'agg-1');

        $this->assertTrue(true);
    }

    public function test_publish_does_nothing_when_events_are_empty(): void
    {
        $uow = $this->createMock(OutboxUnitOfWorkInterface::class);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $uow->expects($this->never())->method('register');

        $publisher = new OutboxEventPublisher($uow, $tx);
        $publisher->publish([], 'agg-1');

        $this->assertTrue(true);
    }
}
