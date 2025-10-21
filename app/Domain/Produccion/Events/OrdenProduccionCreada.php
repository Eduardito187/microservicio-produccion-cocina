<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;
use DateTimeImmutable;

class OrdenProduccionCreada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private readonly DateTimeImmutable $fecha;

    /**
     * @var string
     */
    private readonly int|string $sucursalId;

    /**
     * Constructor
     * 
     * @param string|int|null $opId
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     */
    public function __construct(
        string|int|null $opId,
        DateTimeImmutable $fecha,
        int|string $sucursalId
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
            'sucursalId' => $this->sucursalId
        ];
    }
}