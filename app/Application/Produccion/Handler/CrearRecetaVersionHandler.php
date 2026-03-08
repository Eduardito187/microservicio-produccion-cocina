<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearRecetaVersion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Events\RecetaCreada;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;

/**
 * @class CrearRecetaVersionHandler
 */
class CrearRecetaVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    private $recetaVersionRepository;

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
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int
     */
    public function __invoke(CrearRecetaVersion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $recetaVersion = new RecetaVersion(
                null,
                $command->nombre,
                $command->nutrientes,
                $command->ingredientes,
                $command->description,
                $command->instructions,
                $command->totalCalories
            );

            $id = $this->recetaVersionRepository->save($recetaVersion);
            $event = new RecetaCreada(
                $id,
                $command->nombre,
                $command->nutrientes,
                $command->ingredientes,
                $command->description,
                $command->instructions,
                $command->totalCalories
            );
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
