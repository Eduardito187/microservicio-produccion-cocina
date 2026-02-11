<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarPaquete
 * @package App\Application\Produccion\Command
 */
class ActualizarPaquete
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string|int|null
     */
    public$etiquetaId;

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
     *
     * @param string|int $id
     * @param string|int|null $etiquetaId
     * @param string|int|null $ventanaId
     * @param string|int|null $direccionId
     */
    public function __construct(
        string|int $id,
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
