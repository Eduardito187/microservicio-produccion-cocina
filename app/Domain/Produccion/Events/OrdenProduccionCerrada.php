<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

/**
 * @class OrdenProduccionCerrada
 * @package App\Domain\Produccion\Events
 */
class OrdenProduccionCerrada extends BaseDomainEvent
{
    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * Constructor
     *
     * @param string|int|null $opId
     * @param DateTimeImmutable $fecha
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha
    ) {
        parent::__construct($opId);
        $this->fecha = $fecha;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM)
        ];
    }
}
