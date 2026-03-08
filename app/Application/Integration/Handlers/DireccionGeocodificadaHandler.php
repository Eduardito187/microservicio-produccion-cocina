<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionGeocodificadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class DireccionGeocodificadaHandler
 */
class DireccionGeocodificadaHandler implements IntegrationEventHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionGeocodificadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->geo === null) {
                $this->logger->warning('Direccion geocodificada ignorada (falta geo)', [
                    'direccion_id' => $event->id,
                ]);

                return;
            }

            try {
                $direccion = $this->direccionRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('Direccion geocodificada ignorada (direccion no encontrada)', [
                    'direccion_id' => $event->id,
                ]);

                return;
            }

            $direccion->geo = $event->geo;
            $this->direccionRepository->save($direccion);
        });
    }
}
