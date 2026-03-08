<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearCalendarioItem
 */
class CrearCalendarioItem
{
    /**
     * @var string
     */
    public $calendarioId;

    /**
     * @var string
     */
    public $itemDespachoId;

    /**
     * Constructor
     */
    public function __construct(string $calendarioId, string $itemDespachoId)
    {
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
