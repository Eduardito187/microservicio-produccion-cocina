<?php

namespace Tests\Unit\Application\Produccion;

use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use PHPUnit\Framework\TestCase;

class RegistrarInboundEventHandlerTest extends TestCase
{
    private function tx(): TransactionAggregate
    {
        $tm = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed { return $callback(); }
            public function afterCommit(callable $callback): void { /* no-op */ }
        };

        return new TransactionAggregate($tm);
    }

    public function test_si_event_id_ya_existe_retorna_true_y_no_guarda(): void
    {
        $repo = $this->createMock(InboundEventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('existsByEventId')
            ->with('evt-1')
            ->willReturn(true);

        $repo->expects($this->never())->method('save');

        $handler = new RegistrarInboundEventHandler($repo, $this->tx());

        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-1',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":1}'
        ));

        $this->assertTrue($duplicate);
    }

    public function test_si_event_id_no_existe_guarda_y_retorna_false(): void
    {
        $repo = $this->createMock(InboundEventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('existsByEventId')
            ->with('evt-2')
            ->willReturn(false);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (InboundEvent $e): bool {
                return $e->id === null
                    && $e->eventId === 'evt-2'
                    && $e->eventName === 'SomeEvent'
                    && $e->occurredOn === '2026-01-10T10:00:00Z'
                    && $e->payload === '{"x":2}';
            }))
            ->willReturn(1);

        $handler = new RegistrarInboundEventHandler($repo, $this->tx());

        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-2',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":2}'
        ));

        $this->assertFalse($duplicate);
    }
}