<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\ValueObjects\OccurredOn;

/**
 * @class ProgresoEntregaOrden
 * @package App\Domain\Produccion\Aggregate
 */
class ProgresoEntregaOrden
{
    /**
     * @var string
     */
    private $opId;

    /**
     * @var int
     */
    private $totalPackages;

    /**
     * @var int
     */
    private $completedPackages;

    /**
     * @var int
     */
    private $pendingPackages;

    /**
     * @var ?OccurredOn
     */
    private $allCompletedAt;

    /**
     * @param string $opId
     * @param int $totalPackages
     * @param int $completedPackages
     */
    public function __construct(string $opId, int $totalPackages, int $completedPackages)
    {
        $this->opId = $opId;
        $this->totalPackages = max(0, $totalPackages);
        $this->completedPackages = max(0, $completedPackages);
        $this->pendingPackages = max(0, $this->totalPackages - $this->completedPackages);
        $this->allCompletedAt = null;
    }

    /**
     * @param OccurredOn $occurredOn
     * @return bool
     */
    public function markAllCompletedIfReady(OccurredOn $occurredOn): bool
    {
        if ($this->totalPackages === 0 || $this->completedPackages < $this->totalPackages) {
            return false;
        }

        if ($this->allCompletedAt !== null) {
            return false;
        }

        $this->allCompletedAt = $occurredOn;
        return true;
    }

    /**
     * @return int
     */
    public function pendingPackages(): int
    {
        return $this->pendingPackages;
    }
}
