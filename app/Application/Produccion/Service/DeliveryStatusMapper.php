<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Service;

use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Mapea y parsea los valores de estado, fecha y driver provenientes del evento de logistica.
 *
 * @class DeliveryStatusMapper
 */
class DeliveryStatusMapper
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger;
    }

    /**
     * Convierte el string de estado externo al PackageStatus interno y su KPI asociado.
     *
     * @return array{0: PackageStatus, 1: ?string}
     */
    public function mapStatus(string $status): array
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'entregado', 'delivered', 'confirmada', 'confirmado', 'completed' => [new PackageStatus('confirmada'), 'entrega_confirmada'],
            'fallido', 'fallida', 'failed' => [new PackageStatus('fallida'), 'entrega_fallida'],
            'entransito', 'en_transito', 'en transito', 'intransit', 'onroute', 'en_ruta' => [new PackageStatus('en_ruta'), 'paquete_en_ruta'],
            default => [new PackageStatus('estado_actualizado'), null],
        };
    }

    public function parseOccurredOn(?string $value): ?OccurredOn
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new OccurredOn($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function parseDriverId(?string $value): ?DriverId
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DriverId($value);
        } catch (\Throwable $e) {
            $this->logger->warning('driver_id ignorado porque el formato es invalido', ['driver_id' => $value]);

            return null;
        }
    }

    public function parseStoredStatus(?string $status): ?PackageStatus
    {
        if (! is_string($status) || trim($status) === '') {
            return null;
        }

        try {
            return new PackageStatus($status);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
