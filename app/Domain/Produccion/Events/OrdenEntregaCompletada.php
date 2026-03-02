<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;
use DateTimeZone;

/**
 * @class OrdenEntregaCompletada
 * @package App\Domain\Produccion\Events
 */
class OrdenEntregaCompletada extends BaseDomainEvent
{
    /**
     * @var ?string
     */
    private $entregaId;

    /**
     * @var int
     */
    private $totalPackages;

    /**
     * @var int
     */
    private $confirmedPackages;

    /**
     * @var int
     */
    private $failedPackages;

    /**
     * @var DateTimeImmutable
     */
    private $completedAt;

    /**
     * @param string|int|null $ordenProduccionId
     * @param ?string $entregaId
     * @param int $totalPackages
     * @param int $confirmedPackages
     * @param int $failedPackages
     * @param DateTimeImmutable $completedAt
     */
    public function __construct(
        string|int|null $ordenProduccionId,
        ?string $entregaId,
        int $totalPackages,
        int $confirmedPackages,
        int $failedPackages,
        DateTimeImmutable $completedAt
    ) {
        parent::__construct($ordenProduccionId);
        $this->entregaId = $entregaId;
        $this->totalPackages = $totalPackages;
        $this->confirmedPackages = $confirmedPackages;
        $this->failedPackages = $failedPackages;
        $this->completedAt = $completedAt;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $utc = $this->completedAt->setTimezone(new DateTimeZone('UTC'));

        return [
            'id' => (string) $this->aggregateId(),
            'ordenProduccionId' => (string) $this->aggregateId(),
            'entregaId' => $this->entregaId,
            'totalPackages' => $this->totalPackages,
            'confirmedPackages' => $this->confirmedPackages,
            'failedPackages' => $this->failedPackages,
            'completedAt' => $utc->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
