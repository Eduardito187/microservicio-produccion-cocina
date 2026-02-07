<?php

namespace App\Application\Produccion\Command;

class ActualizarCalendarioItem
{
    /**
     * @var int
     */
    public string $id;

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



