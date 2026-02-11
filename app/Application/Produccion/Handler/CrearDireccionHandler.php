<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Produccion\Command\CrearDireccion;
use App\Domain\Produccion\Events\DireccionCreada;
use App\Domain\Produccion\Entity\Direccion;

/**
 * @class CrearDireccionHandler
 * @package App\Application\Produccion\Handler
 */
class CrearDireccionHandler
{
    /**
     * @var DireccionRepositoryInterface
     */
    private $direccionRepository;

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
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param CrearDireccion $command
     * @return int
     */
    public function __invoke(CrearDireccion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $direccion = new Direccion(
                null,
                $command->nombre,
                $command->linea1,
                $command->linea2,
                $command->ciudad,
                $command->provincia,
                $command->pais,
                $command->geo
            );

            $id = $this->direccionRepository->save($direccion);
            $event = new DireccionCreada(
                $id,
                $command->nombre,
                $command->linea1,
                $command->linea2,
                $command->ciudad,
                $command->provincia,
                $command->pais,
                $command->geo
            );
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
