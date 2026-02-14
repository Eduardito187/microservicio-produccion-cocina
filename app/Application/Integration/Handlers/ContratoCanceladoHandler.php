<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Integration\Events\ContratoCanceladoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use DateTimeImmutable;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
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
        $event = ContratoCanceladoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $suscripcion = $this->suscripcionRepository->byId($event->contratoId);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('Contrato cancelado ignored (contrato not found)', [
                    'contrato_id' => $event->contratoId,
                ]);
                return;
            }

            $suscripcion->estado = 'CANCELADA';
            $suscripcion->motivoCancelacion = $event->motivoCancelacion;
            $suscripcion->canceladoAt = (new DateTimeImmutable('now'))->format(DATE_ATOM);

            $this->suscripcionRepository->save($suscripcion);
        });
    }
}
