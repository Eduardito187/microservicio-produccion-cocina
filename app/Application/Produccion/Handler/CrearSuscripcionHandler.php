<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearSuscripcion;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\SuscripcionCreada;

class CrearSuscripcionHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    public readonly SuscripcionRepositoryInterface $suscripcionRepository;

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








