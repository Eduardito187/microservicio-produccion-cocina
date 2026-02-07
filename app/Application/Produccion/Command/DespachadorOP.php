<?php

namespace App\Application\Produccion\Command;

class DespachadorOP
{
    /**
     * @var string|int
     */
    public readonly string $ordenProduccionId;

    /**
     * @var array
     */
    public readonly array $itemsDespacho;

    /**
     * @var string|int|null
     */
    public readonly string|int|null $pacienteId;

    /**
     * @var string|int|null
     */
    public readonly string|int|null $direccionId;

    /**
     * @var string|int|null
     */
    public readonly string|int|null $ventanaEntrega;

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



