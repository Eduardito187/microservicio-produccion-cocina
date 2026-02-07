<?php

namespace App\Application\Produccion\Command;

class CrearCalendarioItem
{
    /**
     * @var int
     */
    public string $calendarioId;

    /**
     * @var int
     */
    public string $itemDespachoId;

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



