<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class ItemDespacho
 * @package App\Domain\Produccion\Entity
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
    public $recetaVersionId;

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
     * Constructor
     *
     * @param string|int|null $id
     * @param string|int $ordenProduccionId
     * @param string|int $productId
     * @param string|int|null $paqueteId
     * @param string|int|null $recetaVersionId
     * @param string|int|null $pacienteId
     * @param string|int|null $direccionId
     * @param string|int|null $ventanaEntregaId
     */
    public function __construct(
        string|int|null $id,
        string|int $ordenProduccionId,
        string|int $productId,
        string|int|null $paqueteId,
        string|int|null $recetaVersionId = null,
        string|int|null $pacienteId = null,
        string|int|null $direccionId = null,
        string|int|null $ventanaEntregaId = null
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
