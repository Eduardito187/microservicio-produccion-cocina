<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarRecetaVersion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\RecetaVersionActualizada;

class ActualizarRecetaVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    public readonly RecetaVersionRepositoryInterface $recetaVersionRepository;

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
     * @param RecetaVersionRepositoryInterface $recetaVersionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param ActualizarRecetaVersion $command
     * @return int
     */
    public function __invoke(ActualizarRecetaVersion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $recetaVersion = $this->recetaVersionRepository->byId($command->id);
            $recetaVersion->nombre = $command->nombre;
            $recetaVersion->nutrientes = $command->nutrientes;
            $recetaVersion->ingredientes = $command->ingredientes;
            $recetaVersion->version = $command->version;

            $id = $this->recetaVersionRepository->save($recetaVersion);
            $event = new RecetaVersionActualizada(
                $id,
                $recetaVersion->nombre,
                $recetaVersion->version,
                $recetaVersion->nutrientes,
                $recetaVersion->ingredientes
            );
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}








