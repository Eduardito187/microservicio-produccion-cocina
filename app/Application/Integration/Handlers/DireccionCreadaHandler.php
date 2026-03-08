<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionCreadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;

/**
 * @class DireccionCreadaHandler
 */
class DireccionCreadaHandler implements IntegrationEventHandlerInterface
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
     * Constructor
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionCreadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $direccion = new Direccion(
                $event->id,
                $event->nombre,
                $event->linea1,
                $event->linea2,
                $event->ciudad,
                $event->provincia,
                $event->pais,
                $event->geo
            );
            $this->direccionRepository->save($direccion);
        });
    }
}
