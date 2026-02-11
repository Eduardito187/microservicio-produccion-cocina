<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Integration\Events\SuscripcionActualizadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Suscripcion;

/**
 * @class SuscripcionActualizadaHandler
 * @package App\Application\Integration\Handlers
 */
class SuscripcionActualizadaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = SuscripcionActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->suscripcionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                logger()->warning('Suscripcion update ignored (missing nombre for create)', [
                    'suscripcion_id' => $event->id,
                ]);
                return;
            }

            $suscripcion = $existing ?? new Suscripcion(
                $event->id,
                $event->nombre ?? ''
            );

            if ($event->nombre !== null) {
                $suscripcion->nombre = $event->nombre;
            }

            $this->suscripcionRepository->save($suscripcion);
        });
    }
}
