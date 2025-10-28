<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Shared\Events\BaseDomainEvent;

class ProduccionBatchCreado extends BaseDomainEvent
{

    /**
     * @var string
     */
    private readonly string $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $estacionId;

    /**
     * @var Qty
     */
    private readonly Qty $qty;

    /**
     * @var int
     */
    public readonly int $posicion;

    /**
     * Constructor
     * 
     * @param int $id
     * @param int $ordenProduccionId
     * @param int $estacionId
     * @param Qty $qty
     * @param int $posicion
     */
    public function __construct(
        int $id,
        int $ordenProduccionId,
        int $estacionId,
        Qty $qty,
        int $posicion
    ) {
        $this->ordenProduccionId = $ordenProduccionId;
        $this->estacionId = $estacionId;
        $this->qty = $qty;
        $this->posicion = $posicion;
        parent::__construct($id);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'batch_id' => $this->aggregateId(),
            'ordenProduccionId' => $this->ordenProduccionId,
            'estacionId' => $this->estacionId,
            'qty' => $this->qty->value(),
            'posicion' => $this->posicion
        ];
    }
}