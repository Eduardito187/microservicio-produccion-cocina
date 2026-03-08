<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

/**
 * @class CalendarioCreado
 */
class CalendarioCreado extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $calendarioId,
        DateTimeImmutable $fecha
    ) {
        parent::__construct($calendarioId);
        $this->fecha = $fecha;
    }

    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM),
        ];
    }
}
