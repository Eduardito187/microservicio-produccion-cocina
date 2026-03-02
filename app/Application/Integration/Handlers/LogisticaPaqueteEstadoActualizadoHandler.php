<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Events\OrdenEntregaCompletada;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class LogisticaPaqueteEstadoActualizadoHandler
 * @package App\Application\Integration\Handlers
 */
class LogisticaPaqueteEstadoActualizadoHandler implements IntegrationEventHandlerInterface
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
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignored (missing event_id)');
            return;
        }

        $packageId = $this->getString($payload, ['packageId', 'paqueteId', 'package_id', 'paquete_id']);
        if ($packageId === null || $packageId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignored (missing package id)', [
                'event_id' => $eventId,
            ]);
            return;
        }

        $deliveryStatus = $this->getString($payload, ['deliveryStatus', 'delivery_status', 'status']) ?? '';
        [$internalStatus, $kpiName] = $this->mapStatus($deliveryStatus);

        $occurredOn = $this->getString($payload, ['occurredOn', 'occurred_on', 'updatedAt', 'updated_at', 'timestamp']);
        $occurredAt = null;
        if (is_string($occurredOn) && $occurredOn !== '') {
            try {
                $occurredAt = (new DateTimeImmutable($occurredOn))->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $occurredAt = null;
            }
        }

        $evidence = $payload['deliveryEvidence'] ?? null;
        $fotoUrl = null;
        $geo = null;
        if (is_array($evidence)) {
            $fotoUrl = $evidence['url'] ?? ($evidence['fotoUrl'] ?? null);
            $geo = $evidence['geo'] ?? ($evidence['geolocation'] ?? null);
            if (!is_array($geo)) {
                $geo = null;
            }
            if (!is_string($fotoUrl)) {
                $fotoUrl = null;
            }
        }

        $this->transactionAggregate->runTransaction(function () use (
            $eventId,
            $packageId,
            $internalStatus,
            $fotoUrl,
            $geo,
            $occurredAt,
            $payload,
            $kpiName
        ): void {
            $this->evidenciaRepository->upsertByEventId($eventId, [
                'paquete_id' => $packageId,
                'status' => $internalStatus,
                'foto_url' => $fotoUrl,
                'geo' => $geo,
                'occurred_on' => $occurredAt,
                'payload' => $payload,
            ]);

            if ($kpiName !== null) {
                $this->kpiRepository->increment($kpiName, 1);
            }

            $this->syncDispatchStatusAndEmitCompletionIfReady(
                $packageId,
                $internalStatus,
                $occurredAt
            );
        });
    }

    /**
     * Updates item_despacho status and emits order completion event when all packages are terminal.
     *
     * @param string $packageId
     * @param string $status
     * @param ?string $occurredAt
     * @return void
     */
    private function syncDispatchStatusAndEmitCompletionIfReady(string $packageId, string $status, ?string $occurredAt): void
    {
        DB::table('item_despacho')
            ->where('paquete_id', $packageId)
            ->update([
                'delivery_status' => $status,
                'delivery_occurred_on' => $occurredAt,
                'updated_at' => now(),
            ]);

        $opIds = DB::table('item_despacho')
            ->where('paquete_id', $packageId)
            ->whereNotNull('op_id')
            ->pluck('op_id')
            ->unique()
            ->values()
            ->all();

        foreach ($opIds as $opId) {
            if (!is_string($opId) || $opId === '') {
                continue;
            }

            $totalPackages = (int) DB::table('item_despacho')
                ->where('op_id', $opId)
                ->whereNotNull('paquete_id')
                ->distinct()
                ->count('paquete_id');

            if ($totalPackages === 0) {
                continue;
            }

            $confirmedPackages = (int) DB::table('item_despacho')
                ->where('op_id', $opId)
                ->whereNotNull('paquete_id')
                ->where('delivery_status', 'confirmada')
                ->distinct()
                ->count('paquete_id');

            if ($confirmedPackages < $totalPackages) {
                continue;
            }

            $failedPackages = (int) DB::table('item_despacho')
                ->where('op_id', $opId)
                ->whereNotNull('paquete_id')
                ->where('delivery_status', 'fallida')
                ->distinct()
                ->count('paquete_id');

            $entregaId = DB::table('item_despacho')
                ->where('op_id', $opId)
                ->whereNotNull('entrega_id')
                ->orderBy('id')
                ->value('entrega_id');
            $entregaId = is_string($entregaId) && $entregaId !== '' ? $entregaId : null;

            $completedAt = new DateTimeImmutable($occurredAt ?? 'now');
            $marked = DB::table('orden_produccion')
                ->where('id', $opId)
                ->whereNull('entrega_completada_at')
                ->update([
                    'entrega_completada_at' => $completedAt->format('Y-m-d H:i:s'),
                    'updated_at' => now(),
                ]);

            if ($marked > 0) {
                $event = new OrdenEntregaCompletada(
                    $opId,
                    $entregaId,
                    $totalPackages,
                    $confirmedPackages,
                    $failedPackages,
                    $completedAt
                );
                $this->eventPublisher->publish([$event], $opId);
            }
        }
    }

    /**
     * @param string $status
     * @return array{0:string,1:?string}
     */
    private function mapStatus(string $status): array
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'entregado', 'delivered', 'confirmada', 'confirmado' => ['confirmada', 'entrega_confirmada'],
            'fallido', 'fallida', 'failed' => ['fallida', 'entrega_fallida'],
            'entransito', 'en_transito', 'en transito', 'intransit', 'onroute', 'en_ruta' => ['en_ruta', 'paquete_en_ruta'],
            default => ['estado_actualizado', null],
        };
    }

    /**
     * @param array $payload
     * @param array $keys
     * @return ?string
     */
    private function getString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if ($value === null || $value === '') {
                return null;
            }
            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
            if (is_string($value)) {
                return $value;
            }
        }

        return null;
    }
}
