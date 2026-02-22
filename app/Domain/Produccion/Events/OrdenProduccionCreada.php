<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;
use DateTimeZone;

/**
 * @class OrdenProduccionCreada
 * @package App\Domain\Produccion\Events
 */
class OrdenProduccionCreada extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var int
     */
    private $itemsCount;

    /**
     * @var int
     */
    private $batchesCount;

    /**
     * @var int
     */
    private $itemsDespachoCount;

    /**
     * Constructor
     *
     * @param string|int|null $opId
     * @param DateTimeImmutable $fecha
     * @param string $estado
     * @param int $itemsCount
     * @param int $batchesCount
     * @param int $itemsDespachoCount
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha,
        string $estado = 'CREADA',
        int $itemsCount = 0,
        int $batchesCount = 0,
        int $itemsDespachoCount = 0
    ) {
        parent::__construct($opId);
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->itemsCount = $itemsCount;
        $this->batchesCount = $batchesCount;
        $this->itemsDespachoCount = $itemsDespachoCount;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $utc = $this->fecha->setTimezone(new DateTimeZone('UTC'));

        return [
            'id' => (string) $this->aggregateId(),
            'ordenProduccionId' => (string) $this->aggregateId(),
            'fecha' => $utc->format('Y-m-d\TH:i:s\Z'),
            'estado' => $this->estado,
            'itemsCount' => $this->itemsCount,
            'batchesCount' => $this->batchesCount,
            'itemsDespachoCount' => $this->itemsDespachoCount,
        ];
    }
}
