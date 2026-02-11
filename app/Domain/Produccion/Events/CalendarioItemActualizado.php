<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class CalendarioItemActualizado
 * @package App\Domain\Produccion\Events
 */
class CalendarioItemActualizado extends BaseDomainEvent
{
    /**
     * @var string|int
     */
    private $calendarioId;

    /**
     * @var string|int
     */
    private $itemDespachoId;

    /**
     * Constructor
     *
     * @param string|int|null $calendarioItemId
     * @param string|int $calendarioId
     * @param string|int $itemDespachoId
     */
    public function __construct(
        string|int|null $calendarioItemId,
        string|int $calendarioId,
        string|int $itemDespachoId
    ) {
        parent::__construct($calendarioItemId);
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'calendarioId' => $this->calendarioId,
            'itemDespachoId' => $this->itemDespachoId,
        ];
    }
}
