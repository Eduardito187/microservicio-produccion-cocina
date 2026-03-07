<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Aggregate\ProgresoEntregaOrden;
use App\Domain\Produccion\Aggregate\SeguimientoEntregaPaquete;
use App\Domain\Produccion\Events\EntregaInconsistenciaDetectada;
use App\Domain\Produccion\Events\PaqueteEntregado;
use App\Domain\Produccion\ValueObjects\ContratoId;
use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\EntregaId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class ActualizarEstadoPaqueteDesdeLogisticaHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarEstadoPaqueteDesdeLogisticaHandler
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
     * @var DomainEventPublisherInterface
     */
    private $eventPublisher;

    /**
     * @param EntregaEvidenciaRepositoryInterface $evidenciaRepository
     * @param KpiRepositoryInterface $kpiRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        EntregaEvidenciaRepositoryInterface $evidenciaRepository,
        KpiRepositoryInterface $kpiRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher,
        ?LoggerInterface $logger = null
    ) {
        $this->evidenciaRepository = $evidenciaRepository;
        $this->kpiRepository = $kpiRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param ActualizarEstadoPaqueteDesdeLogisticaCommand $command
     * @return void
     */
    public function __invoke(ActualizarEstadoPaqueteDesdeLogisticaCommand $command): void
    {
        $this->kpiRepository->increment('delivery_events_processed_total', 1);
        [$nextStatus, $kpiName] = $this->mapStatus($command->deliveryStatus);
        $occurredOn = $this->parseOccurredOn($command->occurredOn);
        $occurredAt = $occurredOn?->toDatabase();
        $driverId = $this->parseDriverId($command->driverId);
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
            if (!is_array($geo)) {
                $geo = null;
            }
            if (!is_string($fotoUrl)) {
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

            $this->evidenciaRepository->upsertByEventId($command->eventId, [
                'paquete_id' => $command->packageId,
                'status' => $nextStatus->value(),
                'driver_id' => $driverId?->value(),
                'foto_url' => $fotoUrl,
                'geo' => $geo,
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
        $encodedPayload   = json_encode($payload);

        $existing = DB::table('package_delivery_history')
            ->where('event_id', $eventId)
            ->first();

        if ($existing !== null) {
            DB::table('package_delivery_history')
                ->where('event_id', $eventId)
                ->update([
                    'package_id'      => $packageId,
                    'received_status' => $normalizedStatus,
                    'driver_id'       => $driverId,
                    'evidence'        => $encodedEvidence,
                    'payload'         => $encodedPayload,
                    'occurred_on'     => $occurredAt,
                    'updated_at'      => now(),
                ]);
            return;
        }

        // Se listan todos los campos explícitamente para que 'id' nunca quede fuera
        DB::table('package_delivery_history')->insert([
            'id'              => (string) Str::uuid(),
            'event_id'        => $eventId,
            'package_id'      => $packageId,
            'received_status' => $normalizedStatus,
            'driver_id'       => $driverId,
            'evidence'        => $encodedEvidence,
            'payload'         => $encodedPayload,
            'occurred_on'     => $occurredAt,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function syncDispatchStatusAndEmitCompletionIfReady(string $eventId, string $packageId, PackageStatus $nextStatus, ?OccurredOn $occurredOn, ?DriverId $driverId, array $payload): void
    {
        $this->backfillDeliveryContextForPackage($packageId);

        $rows = DB::table('item_despacho')
            ->select('id', 'op_id', 'delivery_status', 'entrega_id', 'contrato_id')
            ->where('paquete_id', $packageId)
            ->get();

        if ($rows->isEmpty()) {
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
            if ((!is_string($row->op_id) || $row->op_id === '') && !$missingOpAlertRaised) {
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

            $currentStatus = $this->parseStoredStatus(is_string($row->delivery_status) ? $row->delivery_status : null);
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

            if (!$changed && $currentStatus !== null && $currentStatus->value() !== $nextStatus->value()) {
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

            DB::table('item_despacho')->where('id', $row->id)->update($updatePayload);

            if (!$trackingUpserted) {
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

            if ($changed && $nextStatus->isCompleted() && !$packageCompletedMetricCounted) {
                $this->kpiRepository->increment('delivery_packages_completed', 1);
                $packageCompletedMetricCounted = true;
            }

            if (is_string($row->op_id) && $row->op_id !== '') {
                $opIds[$row->op_id] = true;
            }
        }

        if (!$anyChanged) {
            return;
        }

        foreach (array_keys($opIds) as $opId) {
            $projection = $this->syncOrderProgress($opId, $occurredOn);
            if ($projection['total_packages'] === 0) {
                continue;
            }

            $allDelivered = $projection['completed_packages'] === $projection['total_packages'];
            $allFailed = $projection['failed_packages'] === $projection['total_packages'];
            if (!$allDelivered && !$allFailed) {
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
                    [
                        'projection' => $projection,
                        'payload' => $payload,
                    ]
                );
                continue;
            }

            $completionEventId = (string) Str::uuid();
            $markedProgress = DB::table('order_delivery_progress')
                ->where('op_id', $opId)
                ->whereNull('completion_event_id')
                ->update([
                    'completion_event_id' => $completionEventId,
                    'all_completed_at' => $projection['all_completed_at'] ?? now()->format('Y-m-d H:i:s'),
                    'updated_at' => now(),
                ]);

            if ($markedProgress === 0) {
                continue;
            }

            $completedAt = new DateTimeImmutable($projection['all_completed_at'] ?? 'now');
            DB::table('orden_produccion')
                ->where('id', $opId)
                ->whereNull('entrega_completada_at')
                ->update([
                    'entrega_completada_at' => $completedAt->format('Y-m-d H:i:s'),
                    'updated_at' => now(),
                ]);

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
    private function backfillDeliveryContextForPackage(string $packageId): void
    {
        $rows = DB::table('item_despacho')
            ->select('id', 'ventana_entrega_id', 'entrega_id', 'contrato_id')
            ->where('paquete_id', $packageId)
            ->get();

        foreach ($rows as $row) {
            $hasEntrega = is_string($row->entrega_id) && $row->entrega_id !== '';
            $hasContrato = is_string($row->contrato_id) && $row->contrato_id !== '';
            if ($hasEntrega && $hasContrato) {
                continue;
            }

            $ventanaId = is_string($row->ventana_entrega_id) ? $row->ventana_entrega_id : null;
            if ($ventanaId === null || $ventanaId === '') {
                continue;
            }

            $ventana = DB::table('ventana_entrega')->select('entrega_id', 'contrato_id')->where('id', $ventanaId)->first();
            if ($ventana === null) {
                continue;
            }

            $update = [];
            if (!$hasEntrega && isset($ventana->entrega_id) && is_string($ventana->entrega_id) && $ventana->entrega_id !== '') {
                $update['entrega_id'] = $ventana->entrega_id;
            }
            if (!$hasContrato && isset($ventana->contrato_id) && is_string($ventana->contrato_id) && $ventana->contrato_id !== '') {
                $update['contrato_id'] = $ventana->contrato_id;
            }

            if ($update !== []) {
                $update['updated_at'] = now();
                DB::table('item_despacho')->where('id', $row->id)->update($update);
            }
        }
    }

    private function enqueueInconsistency(?string $opId, string $eventId, string $packageId, string $reason, array $payload): void
    {
        $eventIdValue = ($eventId !== '') ? $eventId : null;
        $packageIdValue = ($packageId !== '') ? $packageId : null;
        $opIdValue = ($opId !== null && $opId !== '') ? $opId : null;

        $alreadyExists = false;
        if ($eventIdValue !== null) {
            $alreadyExists = DB::table('delivery_inconsistency_queue')
                ->where('event_id', $eventIdValue)
                ->where('reason', $reason)
                ->exists();
        }

        if (!$alreadyExists) {
            DB::table('delivery_inconsistency_queue')->insert([
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

        DB::table('delivery_inconsistency_queue')
            ->where('event_id', $eventIdValue)
            ->where('reason', $reason)
            ->update([
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

    private function upsertTracking(string $packageId, ?string $opId, ?string $entregaId, ?string $contratoId, ?string $driverId, ?string $status, bool $statusLocked, ?string $completedAt, string $eventId, ?string $occurredAt): void
    {
        $existingTracking = DB::table('package_delivery_tracking')->where('package_id', $packageId)->first();

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
            $values['id'] = (string) \Illuminate\Support\Str::uuid();
        }

        DB::table('package_delivery_tracking')->updateOrInsert(
            ['package_id' => $packageId],
            $values
        );
    }

    private function syncOrderProgress(string $opId, ?OccurredOn $occurredOn): array
    {
        $totalPackages = (int) DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('paquete_id')
            ->distinct()
            ->count('paquete_id');

        $completedPackages = (int) DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('paquete_id')
            ->where('delivery_status', 'confirmada')
            ->distinct()
            ->count('paquete_id');
        $failedPackages = (int) DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('paquete_id')
            ->where('delivery_status', 'fallida')
            ->distinct()
            ->count('paquete_id');

        $progress = new ProgresoEntregaOrden($opId, $totalPackages, $completedPackages);
        $existingProgress = DB::table('order_delivery_progress')->where('op_id', $opId)->first();
        $allCompletedAt = ($existingProgress !== null && isset($existingProgress->all_completed_at) && is_string($existingProgress->all_completed_at))
            ? $existingProgress->all_completed_at
            : null;
        if ($allCompletedAt === null && $progress->markAllCompletedIfReady($occurredOn ?? new OccurredOn(new DateTimeImmutable('now')))) {
            $allCompletedAt = ($occurredOn ?? new OccurredOn(new DateTimeImmutable('now')))->toDatabase();
        }

        $pendingPackages = $progress->pendingPackages();

        $entregaIdRaw = DB::table('item_despacho')->where('op_id', $opId)->whereNotNull('entrega_id')->orderBy('id')->value('entrega_id');
        $entregaId = null;
        if (is_string($entregaIdRaw) && $entregaIdRaw !== '') {
            try {
                $entregaId = (new EntregaId($entregaIdRaw))->value();
            } catch (\Throwable $e) {
                $entregaId = null;
            }
        }

        $contratoIdRaw = DB::table('item_despacho')->where('op_id', $opId)->whereNotNull('contrato_id')->orderBy('id')->value('contrato_id');
        $contratoId = null;
        if (is_string($contratoIdRaw) && $contratoIdRaw !== '') {
            try {
                $contratoId = (new ContratoId($contratoIdRaw))->value();
            } catch (\Throwable $e) {
                $contratoId = null;
            }
        }

        $calendarioIdRaw = DB::table('item_despacho as i')
            ->join('calendario_item as ci', 'ci.item_despacho_id', '=', 'i.id')
            ->where('i.op_id', $opId)
            ->whereNotNull('ci.calendario_id')
            ->orderBy('ci.id')
            ->value('ci.calendario_id');
        $calendarioId = is_string($calendarioIdRaw) && $calendarioIdRaw !== '' ? $calendarioIdRaw : null;

        $progressValues = [
            'total_packages' => $totalPackages,
            'completed_packages' => $completedPackages,
            'pending_packages' => $pendingPackages,
            'all_completed_at' => $allCompletedAt,
            'entrega_id' => $entregaId,
            'contrato_id' => $contratoId,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if ($existingProgress === null) {
            $progressValues['id'] = (string) \Illuminate\Support\Str::uuid();
        }

        DB::table('order_delivery_progress')->updateOrInsert(
            ['op_id' => $opId],
            $progressValues
        );

        return [
            'total_packages' => $totalPackages,
            'completed_packages' => $completedPackages,
            'failed_packages' => $failedPackages,
            'pending_packages' => $pendingPackages,
            'all_completed_at' => $allCompletedAt,
            'entrega_id' => $entregaId,
            'contrato_id' => $contratoId,
            'calendario_id' => $calendarioId,
        ];
    }

    private function parseOccurredOn(?string $value): ?OccurredOn
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new OccurredOn($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDriverId(?string $value): ?DriverId
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DriverId($value);
        } catch (\Throwable $e) {
            $this->logger->warning('driver_id ignorado porque el formato es invalido', ['driver_id' => $value]);
            return null;
        }
    }

    private function parseStoredStatus(?string $status): ?PackageStatus
    {
        if (!is_string($status) || trim($status) === '') {
            return null;
        }

        try {
            return new PackageStatus($status);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $status
     * @return array{0:PackageStatus,1:?string}
     */
    private function mapStatus(string $status): array
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'entregado', 'delivered', 'confirmada', 'confirmado', 'completed' => [new PackageStatus('confirmada'), 'entrega_confirmada'],
            'fallido', 'fallida', 'failed' => [new PackageStatus('fallida'), 'entrega_fallida'],
            'entransito', 'en_transito', 'en transito', 'intransit', 'onroute', 'en_ruta' => [new PackageStatus('en_ruta'), 'paquete_en_ruta'],
            default => [new PackageStatus('estado_actualizado'), null],
        };
    }

    private function isUuid(?string $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        return (bool) preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value);
    }
}
