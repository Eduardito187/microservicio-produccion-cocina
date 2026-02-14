<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionGeocodificadaEvent;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class DireccionGeocodificadaHandler
 * @package App\Application\Integration\Handlers
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
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionGeocodificadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->geo === null) {
                $this->logger->warning('Direccion geocodificada ignored (missing geo)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            try {
                $direccion = $this->direccionRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('Direccion geocodificada ignored (direccion not found)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            $direccion->geo = $event->geo;
            $this->direccionRepository->save($direccion);
        });
    }
}
