<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarCalendarioItem
 */
class ActualizarCalendarioItem
{
    /**
     * @var string
     */
    public $id;

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
    public function __construct(string $id, string $calendarioId, string $itemDespachoId)
    {
        $this->id = $id;
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
