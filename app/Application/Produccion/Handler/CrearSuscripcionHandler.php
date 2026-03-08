<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearSuscripcion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Events\SuscripcionCreada;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

/**
 * @class CrearSuscripcionHandler
 */
class CrearSuscripcionHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

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
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int
     */
    public function __invoke(CrearSuscripcion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $suscripcion = new Suscripcion(null, $command->nombre);

            $id = $this->suscripcionRepository->save($suscripcion);
            $event = new SuscripcionCreada($id, $command->nombre);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
