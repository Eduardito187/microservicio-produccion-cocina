<?php

namespace App\Application\Produccion\Command;

class DespachadorOP
{
    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var array
     */
    public readonly array $itemsDespacho;

    /**
     * @var int
     */
    public readonly int $pacienteId;

    /**
     * @var int
     */
    public readonly int $direccionId;

    /**
     * @var int
     */
    public readonly int $ventanaEntrega;

    /**
     * Constructor
     * 
     * @param array $dataApi
     */
    public function __construct(
        array $dataApi
    ) {
        $this->ordenProduccionId = $dataApi["ordenProduccionId"];
        $this->itemsDespacho = $dataApi["itemsDespacho"];
        $this->pacienteId = $dataApi["pacienteId"];
        $this->direccionId = $dataApi["direccionId"];
        $this->ventanaEntrega = $dataApi["ventanaEntrega"];
    }
}



