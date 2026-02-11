<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearCalendarioItem
 * @package App\Application\Produccion\Command
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
     *
     * @param string $calendarioId
     * @param string $itemDespachoId
     */
    public function __construct(string $calendarioId, string $itemDespachoId)
    {
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
