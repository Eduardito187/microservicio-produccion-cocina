<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

/**
 * @class VentanaEntrega
 */
class VentanaEntrega
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $desde;

    /**
     * @var DateTimeImmutable
     */
    public $hasta;

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
        DateTimeImmutable $desde,
        DateTimeImmutable $hasta,
        ?string $entregaId = null,
        ?string $contratoId = null,
        int|string|null $estado = null
    ) {
        $this->id = $id;
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->estado = $estado;
    }
}
