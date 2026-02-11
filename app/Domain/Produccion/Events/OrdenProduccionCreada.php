<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

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
     * @var int|string
     */
    private $sucursalId;

    /**
     * Constructor
     *
     * @param string|int|null $opId
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha,
        int|string $sucursalId
    ) {
        parent::__construct($opId);
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
            'sucursalId' => $this->sucursalId
        ];
    }
}
