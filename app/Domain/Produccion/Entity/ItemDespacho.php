<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class ItemDespacho
 */
class ItemDespacho
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|int
     */
    public $ordenProduccionId;

    /**
     * @var string|int
     */
    public $productId;

    /**
     * @var string|int|null
     */
    public $paqueteId;

    /**
     * @var string|int|null
     */
    public $pacienteId;

    /**
     * @var string|int|null
     */
    public $direccionId;

    /**
     * @var string|int|null
     */
    public $ventanaEntregaId;

    /**
     * @var string|int|null
     */
    public $entregaId;

    /**
     * @var string|int|null
     */
    public $contratoId;

    /**
     * @var string|int|null
     */
    public $driverId;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        string|int $ordenProduccionId,
        string|int $productId,
        string|int|null $paqueteId,
        string|int|null $pacienteId = null,
        string|int|null $direccionId = null,
        string|int|null $ventanaEntregaId = null,
        string|int|null $entregaId = null,
        string|int|null $contratoId = null,
        string|int|null $driverId = null
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
        $this->pacienteId = $pacienteId;
        $this->direccionId = $direccionId;
        $this->ventanaEntregaId = $ventanaEntregaId;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->driverId = $driverId;
    }
}
