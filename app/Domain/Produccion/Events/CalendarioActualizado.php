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
     * @var string
     */
    private $sucursalId;

    /**
     * Constructor
     *
     * @param string|int|null $calendarioId
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(
        string|int|null $calendarioId,
        DateTimeImmutable $fecha,
        string $sucursalId
    ) {
        parent::__construct($calendarioId);
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha->format(DATE_ATOM),
            'sucursalId' => $this->sucursalId,
        ];
    }
}
