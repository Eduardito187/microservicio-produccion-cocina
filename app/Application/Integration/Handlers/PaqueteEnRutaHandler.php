<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Integration\Events\PaqueteEnRutaEvent;
use App\Application\Analytics\KpiRepositoryInterface;
use DateTimeImmutable;

/**
 * @class PaqueteEnRutaHandler
 * @package App\Application\Integration\Handlers
 */
class PaqueteEnRutaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var EntregaEvidenciaRepositoryInterface
     */
    private $evidenciaRepository;

    /**
     * @var KpiRepositoryInterface
     */
    private $kpiRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param EntregaEvidenciaRepositoryInterface $evidenciaRepository
     * @param KpiRepositoryInterface $kpiRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        EntregaEvidenciaRepositoryInterface $evidenciaRepository,
        KpiRepositoryInterface $kpiRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->evidenciaRepository = $evidenciaRepository;
        $this->kpiRepository = $kpiRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            logger()->warning('PaqueteEnRuta ignored (missing event_id)');
            return;
        }

        $event = PaqueteEnRutaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($eventId, $event, $payload): void {
            $occurred = $event->occurredOn ? new DateTimeImmutable($event->occurredOn) : null;

            $this->evidenciaRepository->upsertByEventId($eventId, [
                'paquete_id' => $event->paqueteId,
                'status' => 'en_ruta',
                'foto_url' => null,
                'geo' => null,
                'occurred_on' => $occurred?->format('Y-m-d H:i:s'),
                'payload' => $payload,
            ]);

            $this->kpiRepository->increment('paquete_en_ruta', 1);
        });
    }
}
