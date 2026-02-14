<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Integration\Events\ContratoCanceladoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class ContratoCanceladoHandler
 * @package App\Application\Integration\Handlers
 */
class ContratoCanceladoHandler implements IntegrationEventHandlerInterface
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
        $event = ContratoCanceladoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $suscripcion = $this->suscripcionRepository->byId($event->contratoId);
            } catch (ModelNotFoundException $e) {
                logger()->warning('Contrato cancelado ignored (contrato not found)', [
                    'contrato_id' => $event->contratoId,
                ]);
                return;
            }

            $suscripcion->estado = 'CANCELADA';
            $suscripcion->motivoCancelacion = $event->motivoCancelacion;
            $suscripcion->canceladoAt = now()->toAtomString();

            $this->suscripcionRepository->save($suscripcion);
        });
    }
}
