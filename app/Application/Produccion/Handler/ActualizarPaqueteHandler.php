<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPaquete;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\PaqueteActualizado;

/**
 * @class ActualizarPaqueteHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarPaqueteHandler
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
     * @param ActualizarPaquete $command
     * @return int
     */
    public function __invoke(ActualizarPaquete $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $paquete = $this->paqueteRepository->byId($command->id);
            $paquete->etiquetaId = $command->etiquetaId;
            $paquete->ventanaId = $command->ventanaId;
            $paquete->direccionId = $command->direccionId;

            $id = $this->paqueteRepository->save($paquete);
            $event = new PaqueteActualizado($id, $paquete->etiquetaId, $paquete->ventanaId, $paquete->direccionId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
