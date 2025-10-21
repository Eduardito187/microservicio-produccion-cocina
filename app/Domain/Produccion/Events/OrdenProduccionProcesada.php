<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

class OrdenProduccionProcesada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private readonly DateTimeImmutable $fecha;

    /**
     * Constructor
     * 
     * @param string|int|null $opId
     * @param DateTimeImmutable $fecha
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha
    ) {
        $this->fecha = $fecha;
        parent::__construct($opId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'op_id' => $this->aggregateId(),
            'fecha' => $this->fecha
        ];
    }
}