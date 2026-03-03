<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;
use App\Domain\Shared\Aggregate\AggregateRoot;

/**
 * @class SeguimientoEntregaPaquete
 * @package App\Domain\Produccion\Aggregate
 */
class SeguimientoEntregaPaquete
{
    use AggregateRoot;

    /**
     * @var string
     */
    private $packageId;

    /**
     * @var ?string
     */
    private $opId;

    /**
     * @var ?string
     */
    private $entregaId;

    /**
     * @var ?string
     */
    private $contratoId;

    /**
     * @var ?PackageStatus
     */
    private $status;

    /**
     * @var bool
     */
    private $statusLocked;

    /**
     * @var ?DriverId
     */
    private $driverId;

    /**
     * @var ?OccurredOn
     */
    private $completedAt;

    /**
     * @param string $packageId
     * @param ?string $opId
     * @param ?string $entregaId
     * @param ?string $contratoId
     * @param ?PackageStatus $status
     * @param bool $statusLocked
     * @param ?DriverId $driverId
     * @param ?OccurredOn $completedAt
     */
    public function __construct(
        string $packageId,
        ?string $opId,
        ?string $entregaId,
        ?string $contratoId,
        ?PackageStatus $status,
        bool $statusLocked,
        ?DriverId $driverId,
        ?OccurredOn $completedAt
    ) {
        $this->packageId = $packageId;
        $this->opId = $opId;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->status = $status;
        $this->statusLocked = $statusLocked;
        $this->driverId = $driverId;
        $this->completedAt = $completedAt;
    }

    /**
     * @param PackageStatus $nextStatus
     * @param ?DriverId $driverId
     * @param OccurredOn $occurredOn
     * @return bool true if state changed
     */
    public function applyStatus(PackageStatus $nextStatus, ?DriverId $driverId, OccurredOn $occurredOn): bool
    {
        if ($this->statusLocked && !$nextStatus->isCompleted()) {
            return false;
        }

        if ($this->status !== null && !$this->status->canTransitionTo($nextStatus)) {
            return false;
        }

        $changed = $this->status === null || $this->status->value() !== $nextStatus->value();
        $this->status = $nextStatus;

        if ($driverId !== null) {
            $this->driverId = $driverId;
        }

        if ($nextStatus->isCompleted()) {
            $this->statusLocked = true;
            if ($this->completedAt === null) {
                $this->completedAt = $occurredOn;
            }
        }

        return $changed;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status !== null && $this->status->isCompleted();
    }
}
