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
    public string|int $id;

    /**
     * @var string|int|null
     */
    public string|int|null $etiquetaId;

    /**
     * @var string|int|null
     */
    public string|int|null $ventanaId;

    /**
     * @var string|int|null
     */
    public string|int|null $direccionId;

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
