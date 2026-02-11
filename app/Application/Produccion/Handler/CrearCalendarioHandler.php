<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Produccion\Command\CrearCalendario;
use App\Domain\Produccion\Events\CalendarioCreado;
use App\Domain\Produccion\Entity\Calendario;

/**
 * @class CrearCalendarioHandler
 * @package App\Application\Produccion\Handler
 */
class CrearCalendarioHandler
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
     * @param CrearCalendario $command
     * @return int
     */
    public function __invoke(CrearCalendario $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $calendario = new Calendario(null, $command->fecha, $command->sucursalId);

            $id = $this->calendarioRepository->save($calendario);
            $event = new CalendarioCreado($id, $command->fecha, $command->sucursalId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
