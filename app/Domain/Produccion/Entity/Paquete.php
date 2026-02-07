<?php

namespace App\Domain\Produccion\Entity;

class Paquete
{
    /**
     * @var string|int|null
     */
    public string|int|null $id;

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
     * @param string|int|null $id
     * @param string|int|null $etiquetaId
     * @param string|int|null $ventanaId
     * @param string|int|null $direccionId
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
