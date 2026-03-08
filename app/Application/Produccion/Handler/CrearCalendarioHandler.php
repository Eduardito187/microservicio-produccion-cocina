<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearCalendario;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Events\CalendarioCreado;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;

/**
 * @class CrearCalendarioHandler
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
     * @return int
     */
    public function __invoke(CrearCalendario $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $calendario = new Calendario(null, $command->fecha);

            $id = $this->calendarioRepository->save($calendario);
            $event = new CalendarioCreado($id, $command->fecha);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
