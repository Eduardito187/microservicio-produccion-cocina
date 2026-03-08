<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PaqueteEntregado
 */
class PaqueteEntregado extends BaseDomainEvent
{
    /**
     * @var ?string
     */
    private $calendarioId;

    /**
     * @var ?string
     */
    private $contratoId;

    /**
     * @var string
     */
    private $estado;

    public function __construct(
        string|int|null $ordenProduccionId,
        ?string $calendarioId,
        ?string $contratoId,
        string $estado
    ) {
        parent::__construct($ordenProduccionId);
        $this->calendarioId = $calendarioId;
        $this->contratoId = $contratoId;
        $this->estado = $estado;
    }

    public function toArray(): array
    {
        return [
            'ordenProduccionId' => (string) $this->aggregateId(),
            'calendarioId' => $this->calendarioId,
            'contratoId' => $this->contratoId,
            'estado' => $this->estado,
        ];
    }
}
