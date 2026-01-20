<?php

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Domain\Produccion\Entity\InboundEvent;
use PHPUnit\Framework\TestCase;

class RegistrarInboundEventHandlerTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @return void
     */
    public function test_si_event_id_ya_existe_retorna_true_y_no_guarda(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByEventId')->with('evt-1')->willReturn(true);
        $repository->expects($this->never())->method('save');
        $handler = new RegistrarInboundEventHandler($repository, $this->tx());
        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-1', 'SomeEvent', '2026-01-10T10:00:00Z', '{"x":1}'
        ));

        $this->assertTrue($duplicate);
    }

    /**
     * @return void
     */
    public function test_si_event_id_no_existe_guarda_y_retorna_false(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByEventId')->with('evt-2')->willReturn(false);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (InboundEvent $event): bool {
                return $event->id === null && $event->eventId === 'evt-2' && $event->eventName === 'SomeEvent' 
                        && $event->occurredOn === '2026-01-10T10:00:00Z' && $event->payload === '{"x":2}';
            }))
            ->willReturn(1);
        $handler = new RegistrarInboundEventHandler($repository, $this->tx());
        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-2',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":2}'
        ));

        $this->assertFalse($duplicate);
    }
}