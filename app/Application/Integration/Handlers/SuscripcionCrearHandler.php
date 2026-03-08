<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * @class SuscripcionCrearHandler
 */
class SuscripcionCrearHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $id = $this->resolveId($payload, $meta);
        $tipoServicio = $this->getString($payload, 'tipoServicio') ?? 'servicio';
        $pacienteId = $this->getString($payload, 'pacienteId');
        $fechaInicio = $this->getString($payload, 'fechaInicio');
        $fechaFin = $this->resolveFechaFin($payload, $fechaInicio);
        $nombre = trim((string) ($this->getString($payload, 'nombre') ?? ($tipoServicio . ' #' . $id)));

        $this->transactionAggregate->runTransaction(function () use (
            $id,
            $nombre,
            $pacienteId,
            $tipoServicio,
            $fechaInicio,
            $fechaFin
        ): void {
            $suscripcion = new Suscripcion(
                $id,
                $nombre,
                $pacienteId,
                $tipoServicio,
                $fechaInicio,
                $fechaFin,
                'ACTIVA'
            );
            $this->suscripcionRepository->save($suscripcion);
        });
    }

    private function resolveId(array $payload, array $meta): string
    {
        foreach (['id', 'suscripcionId', 'contratoId'] as $key) {
            $value = $this->getString($payload, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        foreach (['aggregate_id', 'correlation_id'] as $key) {
            $value = $meta[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return (string) Str::uuid();
    }

    private function resolveFechaFin(array $payload, ?string $fechaInicio): ?string
    {
        $fechaFin = $this->getString($payload, 'fechaFin');
        if ($fechaFin !== null && $fechaFin !== '') {
            return $fechaFin;
        }

        if ($fechaInicio === null || $fechaInicio === '') {
            return null;
        }

        $duracionDias = $payload['duracionDias'] ?? null;
        if (! is_numeric($duracionDias)) {
            return null;
        }

        try {
            return (new DateTimeImmutable($fechaInicio))
                ->modify('+' . (int) $duracionDias . ' days')
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return is_string($value) ? $value : null;
    }
}
