<?php

namespace App\Domain\Produccion\Entity;

class ItemDespacho
{
    /**
     * @var int|null
     */
    public int|null $id;

    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $productId;

    /**
     * @var int|null
     */
    public readonly int|null $paqueteId;

    /**
     * @var int|null
     */
    public readonly int|null $recetaVersionId;

    /**
     * @var int|null
     */
    public readonly int|null $pacienteId;

    /**
     * @var int|null
     */
    public readonly int|null $direccionId;

    /**
     * @var int|null
     */
    public readonly int|null $ventanaEntregaId;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int $productId
     * @param int|null $paqueteId
     * @param int|null $recetaVersionId
     * @param int|null $pacienteId
     * @param int|null $direccionId
     * @param int|null $ventanaEntregaId
     */
    public function __construct(
        int|null $id,
        int $ordenProduccionId,
        int $productId,
        int|null $paqueteId,
        int|null $recetaVersionId = null,
        int|null $pacienteId = null,
        int|null $direccionId = null,
        int|null $ventanaEntregaId = null
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
        $this->recetaVersionId = $recetaVersionId;
        $this->pacienteId = $pacienteId;
        $this->direccionId = $direccionId;
        $this->ventanaEntregaId = $ventanaEntregaId;
    }
}
