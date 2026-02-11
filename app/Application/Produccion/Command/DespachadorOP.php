<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class DespachadorOP
 * @package App\Application\Produccion\Command
 */
class DespachadorOP
{
    /**
     * @var string|int
     */
    public string $ordenProduccionId;

    /**
     * @var array
     */
    public array $itemsDespacho;

    /**
     * @var string|int|null
     */
    public string|int|null $pacienteId;

    /**
     * @var string|int|null
     */
    public string|int|null $direccionId;

    /**
     * @var string|int|null
     */
    public string|int|null $ventanaEntrega;

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
