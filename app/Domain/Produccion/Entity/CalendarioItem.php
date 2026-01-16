<?php

namespace App\Domain\Produccion\Entity;

class CalendarioItem
{
    /**
     * @var int|null
     */
    public int|null $id;

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
     * @param int|null $id
     * @param int $calendarioId
     * @param int $itemDespachoId
     */
    public function __construct(int|null $id, int $calendarioId, int $itemDespachoId)
    {
        $this->id = $id;
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
