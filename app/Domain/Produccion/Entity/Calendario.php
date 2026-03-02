<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

/**
 * @class Calendario
 * @package App\Domain\Produccion\Entity
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
     *
     * @param string|int|null $id
     * @param DateTimeImmutable $fecha
     * @param ?string $entregaId
     * @param ?string $contratoId
     * @param int|string|null $estado
     */
    public function __construct(
        string|int|null $id,
        DateTimeImmutable $fecha,
        ?string $entregaId = null,
        ?string $contratoId = null,
        int|string|null $estado = null
    )
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->estado = $estado;
    }
}
