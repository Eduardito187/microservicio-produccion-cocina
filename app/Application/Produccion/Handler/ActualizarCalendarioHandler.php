<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarCalendario;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Events\CalendarioActualizado;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;

/**
 * @class ActualizarCalendarioHandler
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
    public function __invoke(ActualizarCalendario $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $calendario = $this->calendarioRepository->byId($command->id);
            $calendario->fecha = $command->fecha;

            $id = $this->calendarioRepository->save($calendario);
            $event = new CalendarioActualizado($id, $calendario->fecha);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
