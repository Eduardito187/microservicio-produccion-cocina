<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarDireccion;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\DireccionActualizada;

/**
 * @class ActualizarDireccionHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarDireccionHandler
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
     * @param ActualizarDireccion $command
     * @return int
     */
    public function __invoke(ActualizarDireccion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $direccion = $this->direccionRepository->byId($command->id);
            $direccion->nombre = $command->nombre;
            $direccion->linea1 = $command->linea1;
            $direccion->linea2 = $command->linea2;
            $direccion->ciudad = $command->ciudad;
            $direccion->provincia = $command->provincia;
            $direccion->pais = $command->pais;
            $direccion->geo = $command->geo;

            $id = $this->direccionRepository->save($direccion);
            $event = new DireccionActualizada(
                $id,
                $direccion->nombre,
                $direccion->linea1,
                $direccion->linea2,
                $direccion->ciudad,
                $direccion->provincia,
                $direccion->pais,
                $direccion->geo
            );
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
