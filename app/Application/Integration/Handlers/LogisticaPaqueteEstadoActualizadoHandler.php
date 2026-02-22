<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use DateTimeImmutable;
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
     * @param EntregaEvidenciaRepositoryInterface $evidenciaRepository
     * @param KpiRepositoryInterface $kpiRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
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
        });
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

