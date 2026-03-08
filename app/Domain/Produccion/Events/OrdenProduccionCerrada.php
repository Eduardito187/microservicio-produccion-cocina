<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

/**
 * @class OrdenProduccionCerrada
 */
class OrdenProduccionCerrada extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha
    ) {
        parent::__construct($opId);
        $this->fecha = $fecha;
    }

    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM),
        ];
    }
}
