<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

/**
 * @class CalendarioActualizado
 * @package App\Domain\Produccion\Events
 */
class CalendarioActualizado extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * Constructor
     *
     * @param string|int|null $calendarioId
     * @param DateTimeImmutable $fecha
     */
    public function __construct(
        string|int|null $calendarioId,
        DateTimeImmutable $fecha
    ) {
        parent::__construct($calendarioId);
        $this->fecha = $fecha;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM),
        ];
    }
}
