<?php

namespace App\Domain\Produccion\Entity;

class Paquete
{
    /**
     * @var int|null
     */
    public int|null $id;

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
     * @param int|null $id
     * @param int|null $etiquetaId
     * @param int|null $ventanaId
     * @param int|null $direccionId
     */
    public function __construct(
        int|null $id,
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
