<?php

namespace App\Application\Produccion\Command;

class CrearPaquete
{
    /**
     * @var int|null
     */
    public int|null $etiquetaId;

    /**
     * @var int|null
     */
    public int|null $ventanaId;

    /**
     * @var int|null
     */
    public int|null $direccionId;

    /**
     * Constructor
     *
     * @param int|null $etiquetaId
     * @param int|null $ventanaId
     * @param int|null $direccionId
     */
    public function __construct(
        int|null $etiquetaId,
        int|null $ventanaId,
        int|null $direccionId
    ) {
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }
}
