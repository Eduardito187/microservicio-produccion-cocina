<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarCalendario;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\CalendarioActualizado;

/**
 * @class ActualizarCalendarioHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarCalendarioHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * @var DomainEventPublisherInterface
     */
    private $eventPublisher;

    /**
     * Constructor
     *
     * @param CalendarioRepositoryInterface $calendarioRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param ActualizarCalendario $command
     * @return int
     */
    public function __invoke(ActualizarCalendario $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $calendario = $this->calendarioRepository->byId($command->id);
            $calendario->fecha = $command->fecha;
            $calendario->sucursalId = $command->sucursalId;

            $id = $this->calendarioRepository->save($calendario);
            $event = new CalendarioActualizado($id, $calendario->fecha, $calendario->sucursalId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
