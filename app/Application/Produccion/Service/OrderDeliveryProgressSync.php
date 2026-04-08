<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Service;

use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use App\Domain\Produccion\Aggregate\ProgresoEntregaOrden;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\ValueObjects\ContratoId;
use App\Domain\Produccion\ValueObjects\EntregaId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * Sincroniza el progreso de entrega de una orden de produccion y retorna la proyeccion actualizada.
 *
 * @class OrderDeliveryProgressSync
 */
class OrderDeliveryProgressSync
{
    /**
     * @var ItemDespachoRepositoryInterface
     */
    private $itemDespachoRepository;

    /**
     * @var OrderDeliveryProgressRepositoryInterface
     */
    private $progressRepository;

    public function __construct(
        ItemDespachoRepositoryInterface $itemDespachoRepository,
        OrderDeliveryProgressRepositoryInterface $progressRepository
    ) {
        $this->itemDespachoRepository = $itemDespachoRepository;
        $this->progressRepository = $progressRepository;
    }

    /**
     * Calcula el progreso actual, persiste en order_delivery_progress y retorna la proyeccion.
     *
     * @return array{total_packages:int,completed_packages:int,failed_packages:int,pending_packages:int,all_completed_at:?string,entrega_id:?string,contrato_id:?string,calendario_id:?string}
     */
    public function syncAndGetProjection(string $opId, ?OccurredOn $occurredOn): array
    {
        $totalPackages = $this->itemDespachoRepository->countDistinctPaquetesByOpId($opId);
        $completedPackages = $this->itemDespachoRepository->countDistinctPaquetesByOpIdAndStatus($opId, 'confirmada');
        $failedPackages = $this->itemDespachoRepository->countDistinctPaquetesByOpIdAndStatus($opId, 'fallida');

        $progress = new ProgresoEntregaOrden($opId, $totalPackages, $completedPackages);
        $existingProgress = $this->progressRepository->findByOpId($opId);

        $allCompletedAt = ($existingProgress !== null && isset($existingProgress->all_completed_at) && is_string($existingProgress->all_completed_at))
            ? $existingProgress->all_completed_at
            : null;

        $effectiveOccurredOn = $occurredOn ?? new OccurredOn(new DateTimeImmutable('now'));
        if ($allCompletedAt === null && $progress->markAllCompletedIfReady($effectiveOccurredOn)) {
            $allCompletedAt = $effectiveOccurredOn->toDatabase();
        }

        $pendingPackages = $progress->pendingPackages();

        $entregaId = $this->resolveEntregaId($opId);
        $contratoId = $this->resolveContratoId($opId);
        $calendarioId = $this->itemDespachoRepository->findCalendarioIdByOpId($opId);

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
            $progressValues['id'] = (string) Str::uuid();
        }

        $this->progressRepository->upsertByOpId($opId, $progressValues);

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

    private function resolveEntregaId(string $opId): ?string
    {
        $raw = $this->itemDespachoRepository->findFirstEntregaIdByOpId($opId);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return (new EntregaId($raw))->value();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveContratoId(string $opId): ?string
    {
        $raw = $this->itemDespachoRepository->findFirstContratoIdByOpId($opId);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return (new ContratoId($raw))->value();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
