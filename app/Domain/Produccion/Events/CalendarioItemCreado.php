<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class CalendarioItemCreado
 */
class CalendarioItemCreado extends BaseDomainEvent
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

    public function toArray(): array
    {
        return [
            'calendarioId' => $this->calendarioId,
            'itemDespachoId' => $this->itemDespachoId,
        ];
    }
}
