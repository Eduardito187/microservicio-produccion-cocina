<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Produccion\Service\DeliveryContextBackfiller;
use App\Application\Produccion\Service\DeliveryStatusMapper;
use App\Application\Produccion\Service\OrderDeliveryProgressSync;
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
    private KpiRepositoryInterface $kpiRepository;
    private TransactionAggregate $transactionAggregate;
    private DomainEventPublisherInterface $eventPublisher;
    private DeliveryStatusMapper $statusMapper;
    private DeliveryContextBackfiller $backfiller;
    private OrderDeliveryProgressSync $progressSync;
    private LoggerInterface $logger;

    public function __construct(
        DeliveryHandlerRepositories $repos,
        KpiRepositoryInterface $kpiRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher,
        DeliveryStatusMapper $statusMapper,
        DeliveryContextBackfiller $backfiller,
        OrderDeliveryProgressSync $progressSync,
        ?LoggerInterface $logger = null
    ) {
        $this->repos = $repos;
        $this->kpiRepository = $kpiRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
        $this->statusMapper = $statusMapper;
        $this->backfiller = $backfiller;
        $this->progressSync = $progressSync;
        $this->logger = $logger ?? new NullLogger;
    }

    public function __invoke(ActualizarEstadoPaqueteDesdeLogisticaCommand $command): void
    {
        $this->kpiRepository->increment('delivery_events_processed_total', 1);
        [$nextStatus, $kpiName] = $this->statusMapper->mapStatus($command->deliveryStatus);
        $occurredOn = $this->statusMapper->parseOccurredOn($command->occurredOn);
        $occurredAt = $occurredOn?->toDatabase();
        $driverId = $this->statusMapper->parseDriverId($command->driverId);

        $this->logger->info('Procesando actualizacion de estado logistico del paquete', [
            'event_id' => $command->eventId,
            'package_id' => $command->packageId,
            'driver_id' => $driverId?->value(),
            'incoming_status' => $command->deliveryStatus,
            'mapped_status' => $nextStatus->value(),
        ]);

        $fotoUrl = null;
        $geo = null;
        if (is_array($command->deliveryEvidence)) {
            $fotoUrl = $command->deliveryEvidence['url'] ?? ($command->deliveryEvidence['fotoUrl'] ?? null);
            $geo = $command->deliveryEvidence['geo'] ?? ($command->deliveryEvidence['geolocation'] ?? null);
            if (! is_array($geo)) {
                $geo = null;
            }
            if (! is_string($fotoUrl)) {
                $fotoUrl = null;
            }
        } elseif (is_string($command->deliveryEvidence) && trim($command->deliveryEvidence) !== '') {
            $fotoUrl = trim($command->deliveryEvidence);
        }

        $this->transactionAggregate->runTransaction(function () use ($command, $nextStatus, $kpiName, $occurredOn, $occurredAt, $driverId, $fotoUrl, $geo): void {
            $this->persistHistory(
                $command->eventId,
                $command->packageId,
                $command->deliveryStatus,
                $driverId?->value(),
                $command->deliveryEvidence,
                $command->payload,
                $occurredAt
            );

            $this->repos->evidencia->upsertByEventId($command->eventId, [
                'paquete_id' => $command->packageId,
                'status' => $nextStatus->value(),
                'driver_id' => $driverId?->value(),
                'foto_url' => $fotoUrl,
                'geo' => $geo,
                'incident_type' => $command->incidentType,
                'incident_description' => $command->incidentDescription,
                'occurred_on' => $occurredAt,
                'payload' => $command->payload,
            ]);

            if ($kpiName !== null) {
                $this->kpiRepository->increment($kpiName, 1);
            }

            $this->syncDispatchStatusAndEmitCompletionIfReady(
                $command->eventId,
                $command->packageId,
                $nextStatus,
                $occurredOn,
                $driverId,
                $command->payload
            );
        });
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

    private function syncDispatchStatusAndEmitCompletionIfReady(
        string $eventId,
        string $packageId,
        \App\Domain\Produccion\ValueObjects\PackageStatus $nextStatus,
        ?OccurredOn $occurredOn,
        ?\App\Domain\Produccion\ValueObjects\DriverId $driverId,
        array $payload
    ): void {
        $this->backfiller->backfill($packageId);

        $rows = $this->repos->itemDespacho->findDeliveryRowsByPaqueteId($packageId);

        if (empty($rows)) {
            $this->kpiRepository->increment('alert_package_unknown', 1);
            $this->logger->warning('Actualizacion de estado recibida para paquete desconocido', [
                'event_id' => $eventId,
                'package_id' => $packageId,
                'driver_id' => $driverId?->value(),
            ]);
            $this->enqueueInconsistency(null, $eventId, $packageId, 'package_without_dispatch_relation', $payload);

            return;
        }

        $opIds = [];
        $trackingUpserted = false;
        $packageCompletedMetricCounted = false;
        $missingOpAlertRaised = false;
        $anyChanged = false;

        foreach ($rows as $row) {
            if ((! is_string($row->op_id) || $row->op_id === '') && ! $missingOpAlertRaised) {
                $this->kpiRepository->increment('alert_missing_op_id', 1);
                $this->logger->warning('Actualizacion de estado sin relacion op_id', [
                    'event_id' => $eventId,
                    'package_id' => $packageId,
                    'item_despacho_id' => $row->id,
                    'driver_id' => $driverId?->value(),
                ]);
                $this->enqueueInconsistency(null, $eventId, $packageId, 'missing_op_id_for_package', $payload);
                $missingOpAlertRaised = true;
            }

            $currentStatus = $this->statusMapper->parseStoredStatus(is_string($row->delivery_status) ? $row->delivery_status : null);
            $wasLocked = $currentStatus?->isCompleted() ?? false;
            $seguimiento = new SeguimientoEntregaPaquete(
                $packageId,
                is_string($row->op_id) ? $row->op_id : null,
                is_string($row->entrega_id) ? $row->entrega_id : null,
                is_string($row->contrato_id) ? $row->contrato_id : null,
                $currentStatus,
                $wasLocked,
                null,
                null
            );

            $effectiveOccurredOn = $occurredOn ?? new OccurredOn(new DateTimeImmutable('now'));
            $changed = $seguimiento->applyStatus($nextStatus, $driverId, $effectiveOccurredOn);
            $isLocked = $nextStatus->isCompleted() || $wasLocked;

            if (! $changed && $currentStatus !== null && $currentStatus->value() !== $nextStatus->value()) {
                $this->kpiRepository->increment('delivery_state_blocked_terminal', 1);
                $this->logger->warning('Transicion de estado bloqueada por politica del agregado', [
                    'event_id' => $eventId,
                    'package_id' => $packageId,
                    'op_id' => $row->op_id,
                    'driver_id' => $driverId?->value(),
                    'previous_status' => $currentStatus->value(),
                    'new_status' => $nextStatus->value(),
                    'locked' => $isLocked,
                ]);
            }

            $this->logger->info('Transicion de estado del paquete procesada', [
                'event_id' => $eventId,
                'package_id' => $packageId,
                'op_id' => $row->op_id,
                'driver_id' => $driverId?->value(),
                'previous_status' => $currentStatus?->value(),
                'new_status' => $nextStatus->value(),
                'changed' => $changed,
                'locked' => $isLocked,
            ]);

            $updatePayload = ['updated_at' => now()];
            if ($changed) {
                $updatePayload['delivery_status'] = $nextStatus->value();
                $updatePayload['delivery_occurred_on'] = $effectiveOccurredOn->toDatabase();
            }
            if ($driverId !== null) {
                $updatePayload['driver_id'] = $driverId->value();
            }

            $this->repos->itemDespacho->updateDeliveryFields($row->id, $updatePayload);

            if ($changed && $nextStatus->isCompleted() && is_string($row->ventana_entrega_id) && $row->ventana_entrega_id !== '') {
                $this->repos->ventanaEntrega->desactivar($row->ventana_entrega_id);
            }

            if (! $trackingUpserted) {
                $this->upsertTracking(
                    $packageId,
                    is_string($row->op_id) ? $row->op_id : null,
                    is_string($row->entrega_id) ? $row->entrega_id : null,
                    is_string($row->contrato_id) ? $row->contrato_id : null,
                    $driverId?->value(),
                    $changed ? $nextStatus->value() : ($currentStatus?->value()),
                    $isLocked,
                    $nextStatus->isCompleted() ? $effectiveOccurredOn->toDatabase() : null,
                    $eventId,
                    $effectiveOccurredOn->toDatabase()
                );
                $trackingUpserted = true;
            }

            if ($changed) {
                $anyChanged = true;
            }

            if ($changed && $nextStatus->isCompleted() && ! $packageCompletedMetricCounted) {
                $this->kpiRepository->increment('delivery_packages_completed', 1);
                $packageCompletedMetricCounted = true;
            }

            if (is_string($row->op_id) && $row->op_id !== '') {
                $opIds[$row->op_id] = true;
            }
        }

        if (! $anyChanged) {
            return;
        }

        foreach (array_keys($opIds) as $opId) {
            $projection = $this->progressSync->syncAndGetProjection($opId, $occurredOn);

            if ($projection['total_packages'] === 0) {
                continue;
            }

            $allDelivered = $projection['completed_packages'] === $projection['total_packages'];
            $allFailed = $projection['failed_packages'] === $projection['total_packages'];
            if (! $allDelivered && ! $allFailed) {
                continue;
            }

            if ($projection['calendario_id'] === null || $projection['contrato_id'] === null) {
                $this->kpiRepository->increment('alert_missing_delivery_context', 1);
                $this->logger->warning('Evento consolidado bloqueado por contexto de entrega faltante', [
                    'event_id' => $eventId,
                    'package_id' => $packageId,
                    'op_id' => $opId,
                    'driver_id' => $driverId?->value(),
                    'projection' => $projection,
                ]);
                $this->enqueueInconsistency(
                    $opId,
                    $eventId,
                    $packageId,
                    'missing_delivery_context_for_consolidated_event',
                    ['projection' => $projection, 'payload' => $payload]
                );
                continue;
            }

            $completionEventId = (string) Str::uuid();
            $allCompletedAt = $projection['all_completed_at'] ?? now()->format('Y-m-d H:i:s');

            $markedProgress = $this->repos->progress->markCompletionIfNotSet($opId, $completionEventId, $allCompletedAt);

            if ($markedProgress === 0) {
                continue;
            }

            $completedAt = new DateTimeImmutable($allCompletedAt);
            $this->repos->ordenProduccion->markEntregaCompletada($opId, $completedAt);

            $event = new PaqueteEntregado(
                $opId,
                $projection['calendario_id'],
                $projection['contrato_id'],
                $allDelivered ? 'entregado' : 'no entregado'
            );
            $this->eventPublisher->publish([$event], $opId);
            $this->kpiRepository->increment('delivery_orders_completed', 1);
            $this->logger->info('Evento consolidado de entrega publicado', [
                'event_id' => $eventId,
                'op_id' => $opId,
                'package_id' => $packageId,
                'driver_id' => $driverId?->value(),
                'completion_event_id' => $completionEventId,
                'total_packages' => $projection['total_packages'],
                'completed_packages' => $projection['completed_packages'],
                'failed_packages' => $projection['failed_packages'],
                'estado' => $allDelivered ? 'entregado' : 'no entregado',
            ]);
        }
    }

    private function upsertTracking(
        string $packageId,
        ?string $opId,
        ?string $entregaId,
        ?string $contratoId,
        ?string $driverId,
        ?string $status,
        bool $statusLocked,
        ?string $completedAt,
        string $eventId,
        ?string $occurredAt
    ): void {
        $existingTracking = $this->repos->tracking->findByPackageId($packageId);

        if ($completedAt === null && $existingTracking !== null && isset($existingTracking->completed_at)) {
            $completedAt = is_string($existingTracking->completed_at) ? $existingTracking->completed_at : null;
        }

        $values = [
            'op_id' => $opId,
            'entrega_id' => $entregaId,
            'contrato_id' => $contratoId,
            'driver_id' => $driverId,
            'status' => $status,
            'status_locked' => $statusLocked,
            'completed_at' => $completedAt,
            'last_event_id' => $eventId,
            'last_occurred_on' => $occurredAt,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if ($existingTracking === null) {
            $values['id'] = (string) Str::uuid();
        }

        $this->repos->tracking->upsertByPackageId($packageId, $values);
    }

    private function enqueueInconsistency(?string $opId, string $eventId, string $packageId, string $reason, array $payload): void
    {
        $eventIdValue = ($eventId !== '') ? $eventId : null;
        $packageIdValue = ($packageId !== '') ? $packageId : null;
        $opIdValue = ($opId !== null && $opId !== '') ? $opId : null;

        $alreadyExists = false;
        if ($eventIdValue !== null) {
            $alreadyExists = $this->repos->inconsistency->existsByEventIdAndReason($eventIdValue, $reason);
        }

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
                'event_id' => $eventId,
                'package_id' => $packageId,
                'op_id' => $opId,
                'reason' => $reason,
            ]);

            $event = new EntregaInconsistenciaDetectada($opId, $reason, $eventId, $packageId, $payload);
            $this->eventPublisher->publish([$event], $opId ?? $packageId);

            return;
        }

        $this->repos->inconsistency->updateByEventIdAndReason($eventIdValue, $reason, [
            'package_id' => $packageIdValue,
            'op_id' => $opIdValue,
            'payload' => json_encode($payload),
            'updated_at' => now(),
        ]);

        $this->logger->info('Inconsistencia de entrega deduplicada', [
            'event_id' => $eventId,
            'package_id' => $packageId,
            'op_id' => $opId,
            'reason' => $reason,
        ]);
    }
}
