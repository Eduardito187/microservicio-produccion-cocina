<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\EntregaFallidaEvent;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use DateTimeImmutable;

class EntregaFallidaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly EntregaEvidenciaRepositoryInterface $evidenciaRepository,
        private readonly KpiRepositoryInterface $kpiRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            logger()->warning('EntregaFallida ignored (missing event_id)');
            return;
        }

        $event = EntregaFallidaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($eventId, $event, $payload): void {
            $occurred = $event->occurredOn ? new DateTimeImmutable($event->occurredOn) : null;

            $this->evidenciaRepository->upsertByEventId($eventId, [
                'paquete_id' => $event->paqueteId,
                'status' => 'fallida',
                'foto_url' => null,
                'geo' => null,
                'occurred_on' => $occurred?->format('Y-m-d H:i:s'),
                'payload' => $payload,
            ]);

            $this->kpiRepository->increment('entrega_fallida', 1);
        });
    }
}
