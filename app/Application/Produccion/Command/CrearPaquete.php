<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearPaquete
 */
class CrearPaquete
{
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
        string|int|null $etiquetaId,
        string|int|null $ventanaId,
        string|int|null $direccionId
    ) {
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }
}
