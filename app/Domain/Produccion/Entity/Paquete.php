<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Paquete
 */
class Paquete
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|int|null
     */
    public $etiquetaId;

    /**
     * @var string|int|null
     */
    public $ventanaId;

    /**
     * @var string|int|null
     */
    public $direccionId;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        string|int|null $etiquetaId,
        string|int|null $ventanaId,
        string|int|null $direccionId
    ) {
        $this->id = $id;
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }
}
