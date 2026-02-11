<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Produccion\Command\CrearSuscripcion;
use App\Domain\Produccion\Events\SuscripcionCreada;
use App\Domain\Produccion\Entity\Suscripcion;

/**
 * @class CrearSuscripcionHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
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
     * @param CrearSuscripcion $command
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
