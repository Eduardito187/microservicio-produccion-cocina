<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Domain\Shared\Exception\DuplicateRecordException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @class RegistrarInboundEventHandlerTest
 * @package Tests\Unit\Application\Produccion
 */
class RegistrarInboundEventHandlerTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            /**
             * @param callable $callback
             * @return mixed
             */
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            /**
             * @param callable $callback
             * @return void
             */
            public function afterCommit(callable $callback): void
            {
            }
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @return void
     */
    public function test_si_event_id_ya_existe_retorna_true_por_duplicado(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('save')
            ->willThrowException(new DuplicateRecordException('Duplicate entry inbound_events_event_id_unique'));

        $handler = new RegistrarInboundEventHandler($repository, $this->tx());
        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-1',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":1}',
            1
        ));

        $this->assertTrue($duplicate);
    }

    /**
     * @return void
     */
    public function test_si_error_no_controlado_se_relanzara(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('save')
            ->willThrowException(new RuntimeException('DB timeout'));

        $handler = new RegistrarInboundEventHandler($repository, $this->tx());
        $this->expectException(RuntimeException::class);

        $handler(new RegistrarInboundEvent(
            'evt-3',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":3}',
            1
        ));
    }

    /**
     * @return void
     */
    public function test_si_event_id_no_existe_guarda_y_retorna_false(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (InboundEvent $event): bool {
                return $event->id === null
                    && $event->eventId === 'evt-2'
                    && $event->eventName === 'SomeEvent'
                    && $event->occurredOn === '2026-01-10T10:00:00Z'
                    && $event->payload === '{"x":2}'
                    && $event->schemaVersion === 1
                    && is_string($event->correlationId)
                    && $event->correlationId !== '';
            }))
            ->willReturn('e28e9cc2-5225-40c0-b88b-2341f96d76a3');

        $handler = new RegistrarInboundEventHandler($repository, $this->tx());
        $duplicate = $handler(new RegistrarInboundEvent(
            'evt-2',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":2}',
            1
        ));

        $this->assertFalse($duplicate);
    }

    /**
     * @return void
     */
    public function test_schema_version_no_soportada_lanza_excepcion(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->never())->method('save');
        $handler = new RegistrarInboundEventHandler($repository, $this->tx());

        $this->expectException(\InvalidArgumentException::class);

        $handler(new RegistrarInboundEvent(
            'evt-4',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":4}',
            99
        ));
    }

    /**
     * @return void
     */
    public function test_schema_version_requerida_lanza_excepcion(): void
    {
        $repository = $this->createMock(InboundEventRepositoryInterface::class);
        $repository->expects($this->never())->method('save');
        $handler = new RegistrarInboundEventHandler($repository, $this->tx());

        $this->expectException(\InvalidArgumentException::class);

        $handler(new RegistrarInboundEvent(
            'evt-5',
            'SomeEvent',
            '2026-01-10T10:00:00Z',
            '{"x":5}'
        ));
    }
}
