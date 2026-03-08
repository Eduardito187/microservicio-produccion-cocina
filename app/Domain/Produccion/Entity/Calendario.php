<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

/**
 * @class Calendario
 */
class Calendario
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $fecha;

    /**
     * @var ?string
     */
    public $entregaId;

    /**
     * @var ?string
     */
    public $contratoId;

    /**
     * @var int|string|null
     */
    public $estado;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        DateTimeImmutable $fecha,
        ?string $entregaId = null,
        ?string $contratoId = null,
        int|string|null $estado = null
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->estado = $estado;
    }
}
