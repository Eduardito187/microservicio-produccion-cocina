<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class CalendarioItem
 * @package App\Domain\Produccion\Entity
 */
class CalendarioItem
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|int
     */
    public $calendarioId;

    /**
     * @var string|int
     */
    public $itemDespachoId;

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
