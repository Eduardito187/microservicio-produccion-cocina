<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearPaquete;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Produccion\Events\PaqueteCreado;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;

/**
 * @class CrearPaqueteHandler
 */
class CrearPaqueteHandler
{
    /**
     * @var PaqueteRepositoryInterface
     */
    private $paqueteRepository;

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
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int
     */
    public function __invoke(CrearPaquete $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $paquete = new Paquete(
                null,
                $command->etiquetaId,
                $command->ventanaId,
                $command->direccionId
            );

            $id = $this->paqueteRepository->save($paquete);
            $event = new PaqueteCreado($id, $command->etiquetaId, $command->ventanaId, $command->direccionId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
