<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionGeocodificadaEvent;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * Constructor
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
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
                logger()->warning('Direccion geocodificada ignored (missing geo)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            try {
                $direccion = $this->direccionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                logger()->warning('Direccion geocodificada ignored (direccion not found)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            $direccion->geo = $event->geo;
            $this->direccionRepository->save($direccion);
        });
    }
}
