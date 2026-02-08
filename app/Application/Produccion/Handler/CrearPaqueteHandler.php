<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearPaquete;
use App\Domain\Produccion\Entity\Paquete;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\PaqueteCreado;

class CrearPaqueteHandler
{
    /**
     * @var PaqueteRepositoryInterface
     */
    public readonly PaqueteRepositoryInterface $paqueteRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * @var DomainEventPublisherInterface
     */
    private readonly DomainEventPublisherInterface $eventPublisher;

    /**
     * Constructor
     *
     * @param PaqueteRepositoryInterface $paqueteRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
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
     * @param CrearPaquete $command
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








