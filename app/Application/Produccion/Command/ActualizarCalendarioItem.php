<?php

namespace App\Application\Produccion\Command;

class ActualizarCalendarioItem
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param int $calendarioId
     * @param int $itemDespachoId
     */
    public function __construct(int $id, int $calendarioId, int $itemDespachoId)
    {
        $this->id = $id;
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}



