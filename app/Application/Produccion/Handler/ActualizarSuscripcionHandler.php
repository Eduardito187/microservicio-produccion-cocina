<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarSuscripcion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\SuscripcionActualizada;

/**
 * @class ActualizarSuscripcionHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarSuscripcionHandler
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
     * @param ActualizarSuscripcion $command
     * @return int
     */
    public function __invoke(ActualizarSuscripcion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $suscripcion = $this->suscripcionRepository->byId($command->id);
            $suscripcion->nombre = $command->nombre;

            $id = $this->suscripcionRepository->save($suscripcion);
            $event = new SuscripcionActualizada($id, $suscripcion->nombre);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
