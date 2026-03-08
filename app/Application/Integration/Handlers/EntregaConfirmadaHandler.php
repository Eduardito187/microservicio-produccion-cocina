<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Integration\Events\EntregaConfirmadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class EntregaConfirmadaHandler
 */
class EntregaConfirmadaHandler implements IntegrationEventHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        EntregaEvidenciaRepositoryInterface $evidenciaRepository,
        KpiRepositoryInterface $kpiRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->evidenciaRepository = $evidenciaRepository;
        $this->kpiRepository = $kpiRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (! is_string($eventId) || $eventId === '') {
            $this->logger->warning('EntregaConfirmada ignorada (falta event_id)');

            return;
        }

        $event = EntregaConfirmadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($eventId, $event, $payload): void {
            $occurred = $event->occurredOn ? new DateTimeImmutable($event->occurredOn) : null;

            $this->evidenciaRepository->upsertByEventId($eventId, [
                'paquete_id' => $event->paqueteId,
                'status' => 'confirmada',
                'foto_url' => $event->fotoUrl,
                'geo' => $event->geo,
                'occurred_on' => $occurred?->format('Y-m-d H:i:s'),
                'payload' => $payload,
            ]);

            $this->kpiRepository->increment('entrega_confirmada', 1);
        });
    }
}
