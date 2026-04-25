<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Aggregate\SeguimientoEntregaPaquete;
use App\Domain\Produccion\Events\EntregaInconsistenciaDetectada;
use App\Domain\Produccion\Events\PaqueteEntregado;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use DateTimeImmutable;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Orquesta la actualizacion del estado de entrega de un paquete proveniente del servicio de logistica.
 *
 * @class ActualizarEstadoPaqueteDesdeLogisticaHandler
 */
class ActualizarEstadoPaqueteDesdeLogisticaHandler
{
    private DeliveryHandlerRepositories $repos;
    private DeliveryHandlerServices $services;
    private KpiRepositoryInterface $kpiRepository;
    private TransactionAggregate $transactionAggregate;
    private DomainEventPublisherInterface $eventPublisher;
    private LoggerInterface $logger;

    public function __construct(
        DeliveryHandlerRepositories $repos,
        DeliveryHandlerServices $services,
        KpiRepositoryInterface $kpiRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher,
        ?LoggerInterface $logger = null
    ) {
        $this->repos = $repos;
        $this->services = $services;
        $this->kpiRepository = $kpiRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
        $this->logger = $logger ?? new NullLogger;
    }

    public function __invoke(ActualizarEstadoPaqueteDesdeLogisticaCommand $command): void
    {
        $this->kpiRepository->increment('delivery_events_processed_total', 1);
        [$nextStatus, $kpiName] = $this->services->statusMapper->mapStatus($command->deliveryStatus);
        $occurredOn = $this->services->statusMapper->parseOccurredOn($command->occurredOn);
        $occurredAt = $occurredOn?->toDatabase();
        $driverId = $this->services->statusMapper->parseDriverId($command->driverId);

        $this->logger->info('Procesando actualizacion de estado logistico del paquete', [
            'event_id' => $command->eventId,
            'package_id' => $command->packageId,
            'driver_id' => $driverId?->value(),
            'incoming_status' => $command->deliveryStatus,
            'mapped_status' => $nextStatus->value(),
        ]);

        $ctx = new DeliveryEventContext($command->eventId, $command->packageId, $nextStatus, $occurredOn, $driverId, $command->payload);

        $fotoUrl = $this->extractFotoUrl($command->deliveryEvidence);
        $geo = $this->extractGeo($command->deliveryEvidence);

        $this->transactionAggregate->runTransaction(function () use ($command, $ctx, $kpiName, $occurredAt, $driverId, $fotoUrl, $geo): void {
            $this->persistHistory($ctx->eventId, $ctx->packageId, $command->deliveryStatus, $driverId?->value(), $command->deliveryEvidence, $ctx->payload, $occurredAt);

            $this->repos->evidencia->upsertByEventId($ctx->eventId, [
                'paquete_id' => $ctx->packageId,
                'status' => $ctx->nextStatus->value(),
                'driver_id' => $driverId?->value(),
                'foto_url' => $fotoUrl,
                'geo' => $geo,
                'incident_type' => $command->incidentType,
                'incident_description' => $command->incidentDescription,
                'occurred_on' => $occurredAt,
                'payload' => $ctx->payload,
            ]);

            if ($kpiName !== null) {
                $this->kpiRepository->increment($kpiName, 1);
            }

            $this->syncDispatchStatusAndEmitCompletionIfReady($ctx);
        });
    }

    private function extractFotoUrl(mixed $evidence): ?string
    {
        if (is_array($evidence)) {
            $url = $evidence['url'] ?? ($evidence['fotoUrl'] ?? null);

            return is_string($url) ? $url : null;
        }

        if (is_string($evidence) && trim($evidence) !== '') {
            return trim($evidence);
        }

        return null;
    }

    private function extractGeo(mixed $evidence): ?array
    {
        if (! is_array($evidence)) {
            return null;
        }

        $geo = $evidence['geo'] ?? ($evidence['geolocation'] ?? null);

        return is_array($geo) ? $geo : null;
    }

    private function persistHistory(string $eventId, string $packageId, string $receivedStatus, ?string $driverId, mixed $evidence, array $payload, ?string $occurredAt): void
    {
        $encodedEvidence = null;
        if (is_array($evidence)) {
            $encodedEvidence = json_encode($evidence);
        } elseif (is_string($evidence) && trim($evidence) !== '') {
            $encodedEvidence = json_encode(['value' => $evidence]);
        }

        $normalizedStatus = $receivedStatus !== '' ? strtolower(trim($receivedStatus)) : null;
        $encodedPayload = json_encode($payload);
        $existing = $this->repos->history->findByEventId($eventId);

        if ($existing !== null) {
            $this->repos->history->updateByEventId($eventId, [
                'package_id' => $packageId,
                'received_status' => $normalizedStatus,
                'driver_id' => $driverId,
                'evidence' => $encodedEvidence,
                'payload' => $encodedPayload,
                'occurred_on' => $occurredAt,
                'updated_at' => now(),
            ]);

            return;
        }

        $this->repos->history->insert([
            'id' => (string) Str::uuid(),
            'event_id' => $eventId,
            'package_id' => $packageId,
            'received_status' => $normalizedStatus,
            'driver_id' => $driverId,
            'evidence' => $encodedEvidence,
            'payload' => $encodedPayload,
            'occurred_on' => $occurredAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function syncDispatchStatusAndEmitCompletionIfReady(DeliveryEventContext $ctx): void
    {
        $this->services->backfiller->backfill($ctx->packageId);

        $rows = $this->repos->itemDespacho->findDeliveryRowsByPaqueteId($ctx->packageId);

        if (empty($rows)) {
            $this->kpiRepository->increment('alert_package_unknown', 1);
            $this->logger->warning('Actualizacion de estado recibida para paquete desconocido', [
                'event_id' => $ctx->eventId,
                'package_id' => $ctx->packageId,
                'driver_id' => $ctx->driverId?->value(),
            ]);
            $this->enqueueInconsistency(null, $ctx, 'package_without_dispatch_relation');

            return;
        }

        [$opIds, $anyChanged] = $this->processRowsAndCollectOpIds($rows, $ctx);

        if (! $anyChanged) {
            return;
        }

        foreach (array_keys($opIds) as $opId) {
            $this->emitOrderCompletionIfReady($opId, $ctx);
        }
    }

    /**
     * @return array{0: array<string,true>, 1: bool}
     */
    private function processRowsAndCollectOpIds(array $rows, DeliveryEventContext $ctx): array
    {
        $state = new RowProcessingState;
        $opIds = [];
        $anyChanged = false;

        foreach ($rows as $row) {
            if ($this->processDeliveryRow($row, $ctx, $state)) {
                $anyChanged = true;
            }

            if (is_string($row->op_id) && $row->op_id !== '') {
                $opIds[$row->op_id] = true;
            }
        }

        return [$opIds, $anyChanged];
    }

    private function processDeliveryRow(object $row, DeliveryEventContext $ctx, RowProcessingState $state): bool
    {
        $this->handleMissingOpId($row, $ctx, $state);

        $currentStatus = $this->services->statusMapper->parseStoredStatus(is_string($row->delivery_status) ? $row->delivery_status : null);
        $wasLocked = $currentStatus?->isCompleted() ?? false;
        $seguimiento = new SeguimientoEntregaPaquete(
            $ctx->packageId,
            is_string($row->op_id) ? $row->op_id : null,
            is_string($row->entrega_id) ? $row->entrega_id : null,
            is_string($row->contrato_id) ? $row->contrato_id : null,
            $currentStatus,
            $wasLocked,
            null,
            null
        );

        $effectiveOccurredOn = $ctx->occurredOn ?? new OccurredOn(new DateTimeImmutable('now'));
        $changed = $seguimiento->applyStatus($ctx->nextStatus, $ctx->driverId, $effectiveOccurredOn);
        $isLocked = $ctx->nextStatus->isCompleted() || $wasLocked;

        $this->logStatusTransition($row, $ctx, $currentStatus, $changed, $isLocked);
        $this->updateItemDespacho($row, $ctx, $changed, $effectiveOccurredOn);

        if (! $state->trackingUpserted) {
            $this->upsertTracking($this->buildTrackingContext($row, $ctx, $currentStatus, $changed, $isLocked, $effectiveOccurredOn));
            $state->trackingUpserted = true;
        }

        if ($changed && $ctx->nextStatus->isCompleted()) {
            $this->deactivateVentanaIfExpired($row);
            if (! $state->packageCompletedMetricCounted) {
                $this->kpiRepository->increment('delivery_packages_completed', 1);
                $state->packageCompletedMetricCounted = true;
            }
        }

        return $changed;
    }

    private function handleMissingOpId(object $row, DeliveryEventContext $ctx, RowProcessingState $state): void
    {
        if ((is_string($row->op_id) && $row->op_id !== '') || $state->missingOpAlertRaised) {
            return;
        }

        $this->kpiRepository->increment('alert_missing_op_id', 1);
        $this->logger->warning('Actualizacion de estado sin relacion op_id', [
            'event_id' => $ctx->eventId,
            'package_id' => $ctx->packageId,
            'item_despacho_id' => $row->id,
            'driver_id' => $ctx->driverId?->value(),
        ]);
        $this->enqueueInconsistency(null, $ctx, 'missing_op_id_for_package');
        $state->missingOpAlertRaised = true;
    }

    private function logStatusTransition(object $row, DeliveryEventContext $ctx, mixed $currentStatus, bool $changed, bool $isLocked): void
    {
        if (! $changed && $currentStatus !== null && $currentStatus->value() !== $ctx->nextStatus->value()) {
            $this->kpiRepository->increment('delivery_state_blocked_terminal', 1);
            $this->logger->warning('Transicion de estado bloqueada por politica del agregado', [
                'event_id' => $ctx->eventId,
                'package_id' => $ctx->packageId,
                'op_id' => $row->op_id,
                'driver_id' => $ctx->driverId?->value(),
                'previous_status' => $currentStatus->value(),
                'new_status' => $ctx->nextStatus->value(),
                'locked' => $isLocked,
            ]);
        }

        $this->logger->info('Transicion de estado del paquete procesada', [
            'event_id' => $ctx->eventId,
            'package_id' => $ctx->packageId,
            'op_id' => $row->op_id,
            'driver_id' => $ctx->driverId?->value(),
            'previous_status' => $currentStatus?->value(),
            'new_status' => $ctx->nextStatus->value(),
            'changed' => $changed,
            'locked' => $isLocked,
        ]);
    }

    private function updateItemDespacho(object $row, DeliveryEventContext $ctx, bool $changed, OccurredOn $effectiveOccurredOn): void
    {
        $updatePayload = ['updated_at' => now()];

        if ($changed) {
            $updatePayload['delivery_status'] = $ctx->nextStatus->value();
            $updatePayload['delivery_occurred_on'] = $effectiveOccurredOn->toDatabase();
        }

        if ($ctx->driverId !== null) {
            $updatePayload['driver_id'] = $ctx->driverId->value();
        }

        $this->repos->itemDespacho->updateDeliveryFields($row->id, $updatePayload);
    }

    private function deactivateVentanaIfExpired(object $row): void
    {
        $ventanaId = is_string($row->ventana_entrega_id) ? $row->ventana_entrega_id : null;

        if ($ventanaId !== null && $ventanaId !== '') {
            $this->repos->completion->ventanaEntrega->desactivarSiVencida($ventanaId);
        }
    }

    private function emitOrderCompletionIfReady(string $opId, DeliveryEventContext $ctx): void
    {
        $projection = $this->services->progressSync->syncAndGetProjection($opId, $ctx->occurredOn);

        if ($projection['total_packages'] === 0) {
            return;
        }

        $allDelivered = $projection['completed_packages'] === $projection['total_packages'];
        $allFailed = $projection['failed_packages'] === $projection['total_packages'];

        if ($allDelivered || $allFailed) {
            $this->finalizeOrderCompletion($opId, $ctx, $projection, $allDelivered);
        }
    }

    private function finalizeOrderCompletion(string $opId, DeliveryEventContext $ctx, array $projection, bool $allDelivered): void
    {
        if ($projection['calendario_id'] === null || $projection['contrato_id'] === null) {
            $this->kpiRepository->increment('alert_missing_delivery_context', 1);
            $this->logger->warning('Evento consolidado bloqueado por contexto de entrega faltante', [
                'event_id' => $ctx->eventId,
                'package_id' => $ctx->packageId,
                'op_id' => $opId,
                'driver_id' => $ctx->driverId?->value(),
                'projection' => $projection,
            ]);
            $this->enqueueInconsistency(
                $opId,
                $ctx,
                'missing_delivery_context_for_consolidated_event',
                ['projection' => $projection, 'payload' => $ctx->payload]
            );

            return;
        }

        $completionEventId = (string) Str::uuid();
        $allCompletedAt = $projection['all_completed_at'] ?? now()->format('Y-m-d H:i:s');
        $markedProgress = $this->repos->completion->progress->markCompletionIfNotSet($opId, $completionEventId, $allCompletedAt);

        if ($markedProgress === 0) {
            return;
        }

        $this->repos->completion->ordenProduccion->markEntregaCompletada($opId, new DateTimeImmutable($allCompletedAt));
        $this->eventPublisher->publish([new PaqueteEntregado($opId, $projection['calendario_id'], $projection['contrato_id'], $allDelivered ? 'entregado' : 'no entregado')], $opId);
        $this->kpiRepository->increment('delivery_orders_completed', 1);
        $this->logger->info('Evento consolidado de entrega publicado', [
            'event_id' => $ctx->eventId,
            'op_id' => $opId,
            'package_id' => $ctx->packageId,
            'driver_id' => $ctx->driverId?->value(),
            'completion_event_id' => $completionEventId,
            'total_packages' => $projection['total_packages'],
            'completed_packages' => $projection['completed_packages'],
            'failed_packages' => $projection['failed_packages'],
            'estado' => $allDelivered ? 'entregado' : 'no entregado',
        ]);
    }

    private function buildTrackingContext(object $row, DeliveryEventContext $ctx, mixed $currentStatus, bool $changed, bool $isLocked, OccurredOn $effectiveOccurredOn): TrackingUpdateContext
    {
        return new TrackingUpdateContext(
            packageId: $ctx->packageId,
            context: new DeliveryContextIds(
                opId: is_string($row->op_id) ? $row->op_id : null,
                entregaId: is_string($row->entrega_id) ? $row->entrega_id : null,
                contratoId: is_string($row->contrato_id) ? $row->contrato_id : null,
            ),
            driverId: $ctx->driverId?->value(),
            status: $changed ? $ctx->nextStatus->value() : ($currentStatus?->value()),
            statusLocked: $isLocked,
            completedAt: $ctx->nextStatus->isCompleted() ? $effectiveOccurredOn->toDatabase() : null,
            event: new TrackingEventRef(
                eventId: $ctx->eventId,
                occurredAt: $effectiveOccurredOn->toDatabase(),
            ),
        );
    }

    private function upsertTracking(TrackingUpdateContext $ctx): void
    {
        $existingTracking = $this->repos->tracking->findByPackageId($ctx->packageId);

        $completedAt = $ctx->completedAt;
        if ($completedAt === null && $existingTracking !== null && isset($existingTracking->completed_at)) {
            $completedAt = is_string($existingTracking->completed_at) ? $existingTracking->completed_at : null;
        }

        $values = [
            'op_id' => $ctx->context->opId,
            'entrega_id' => $ctx->context->entregaId,
            'contrato_id' => $ctx->context->contratoId,
            'driver_id' => $ctx->driverId,
            'status' => $ctx->status,
            'status_locked' => $ctx->statusLocked,
            'completed_at' => $completedAt,
            'last_event_id' => $ctx->event->eventId,
            'last_occurred_on' => $ctx->event->occurredAt,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if ($existingTracking === null) {
            $values['id'] = (string) Str::uuid();
        }

        $this->repos->tracking->upsertByPackageId($ctx->packageId, $values);
    }

    private function enqueueInconsistency(?string $opId, DeliveryEventContext $ctx, string $reason, array $extra = []): void
    {
        $eventIdValue = $ctx->eventId !== '' ? $ctx->eventId : null;
        $packageIdValue = $ctx->packageId !== '' ? $ctx->packageId : null;
        $opIdValue = ($opId !== null && $opId !== '') ? $opId : null;
        $payload = empty($extra) ? $ctx->payload : $extra;

        $alreadyExists = $eventIdValue !== null && $this->repos->inconsistency->existsByEventIdAndReason($eventIdValue, $reason);

        if (! $alreadyExists) {
            $this->repos->inconsistency->insert([
                'id' => (string) Str::uuid(),
                'event_id' => $eventIdValue,
                'package_id' => $packageIdValue,
                'op_id' => $opIdValue,
                'reason' => $reason,
                'payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->kpiRepository->increment('delivery_inconsistency_events', 1);
            $this->logger->warning('Inconsistencia de entrega encolada', [
                'event_id' => $ctx->eventId,
                'package_id' => $ctx->packageId,
                'op_id' => $opId,
                'reason' => $reason,
            ]);
            $this->eventPublisher->publish([new EntregaInconsistenciaDetectada($opId, $reason, $ctx->eventId, $ctx->packageId, $payload)], $opId ?? $ctx->packageId);

            return;
        }

        $this->repos->inconsistency->updateByEventIdAndReason($eventIdValue, $reason, [
            'package_id' => $packageIdValue,
            'op_id' => $opIdValue,
            'payload' => json_encode($payload),
            'updated_at' => now(),
        ]);
        $this->logger->info('Inconsistencia de entrega deduplicada', [
            'event_id' => $ctx->eventId,
            'package_id' => $ctx->packageId,
            'op_id' => $opId,
            'reason' => $reason,
        ]);
    }
}
