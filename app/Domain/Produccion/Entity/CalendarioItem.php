<?php

namespace App\Domain\Produccion\Entity;

class CalendarioItem
{
    /**
     * @var string|int|null
     */
    public string|int|null $id;

    /**
     * @var int
     */
    public string|int $calendarioId;

    /**
     * @var int
     */
    public string|int $itemDespachoId;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string|int $calendarioId
     * @param string|int $itemDespachoId
     */
    public function __construct(string|int|null $id, string|int $calendarioId, string|int $itemDespachoId)
    {
        $this->id = $id;
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }
}
