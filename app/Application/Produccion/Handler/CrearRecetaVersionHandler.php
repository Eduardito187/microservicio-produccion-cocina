<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearRecetaVersion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\RecetaVersionCreada;
use App\Domain\Produccion\Entity\RecetaVersion;

/**
 * @class CrearRecetaVersionHandler
 * @package App\Application\Produccion\Handler
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
     * @param CrearRecetaVersion $command
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
                $command->version
            );

            $id = $this->recetaVersionRepository->save($recetaVersion);
            $event = new RecetaVersionCreada(
                $id,
                $command->nombre,
                $command->version,
                $command->nutrientes,
                $command->ingredientes
            );
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
