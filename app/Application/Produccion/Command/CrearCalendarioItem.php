<?php

namespace App\Application\Produccion\Command;

class CrearCalendarioItem
{
    /**
     * @var int
     */
    public int $calendarioId;

    /**
     * @var int
     */
    public int $itemDespachoId;

    /**
     * Constructor
     *
     * @param int $calendarioId
     * @param int $itemDespachoId
     */
    public function __construct(int $calendarioId, int $itemDespachoId)
    {
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
