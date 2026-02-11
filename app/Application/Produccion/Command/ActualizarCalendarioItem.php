<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarCalendarioItem
 * @package App\Application\Produccion\Command
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
     *
     * @param string $id
     * @param string $calendarioId
     * @param string $itemDespachoId
     */
    public function __construct(string $id, string $calendarioId, string $itemDespachoId)
    {
        $this->id = $id;
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
