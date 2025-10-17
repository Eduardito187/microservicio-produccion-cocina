<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\BaseDomainEvent;

class OrdenProduccionCreada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private readonly string $fecha;

    /**
     * @var string
     */
    private readonly string $sucursalId;

    /**
     * Constructor
     * 
     * @param string $opId
     * @param string $fecha
     * @param string $sucursalId
     */
    public function __construct(
        string $opId,
        string $fecha,
        string $sucursalId
    ) {
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        parent::__construct($opId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'op_id' => $this->aggregateId(),
            'fecha' => $this->fecha,
            'sucursalId' => $this->sucursalId,
        ];
    }
}