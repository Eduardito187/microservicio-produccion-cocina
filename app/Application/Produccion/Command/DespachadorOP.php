<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class DespachadorOP
 */
class DespachadorOP
{
    /**
     * @var string|int
     */
    public $ordenProduccionId;

    /**
     * @var array
     */
    public $itemsDespacho;

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
    public $ventanaEntrega;

    /**
     * Constructor
     */
    public function __construct(
        array $dataApi
    ) {
        $this->ordenProduccionId = $dataApi['ordenProduccionId'];
        $this->itemsDespacho = $dataApi['itemsDespacho'];
        $this->pacienteId = $dataApi['pacienteId'];
        $this->direccionId = $dataApi['direccionId'];
        $this->ventanaEntrega = $dataApi['ventanaEntrega'];
    }
}
