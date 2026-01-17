<?php

namespace App\Application\Produccion\Command;

class ActualizarPaquete
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param int|null $etiquetaId
     * @param int|null $ventanaId
     * @param int|null $direccionId
     */
    public function __construct(
        int $id,
        int|null $etiquetaId,
        int|null $ventanaId,
        int|null $direccionId
    ) {
        $this->id = $id;
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }
}



