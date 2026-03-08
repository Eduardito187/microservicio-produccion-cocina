<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

/**
 * @class OrdenProduccionDespachada
 */
class OrdenProduccionDespachada extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * @var int
     */
    private $itemsDespachoCount;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha,
        int $itemsDespachoCount
    ) {
        parent::__construct($opId);
        $this->fecha = $fecha;
        $this->itemsDespachoCount = $itemsDespachoCount;
    }

    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM),
            'itemsDespachoCount' => $this->itemsDespachoCount,
        ];
    }
}
