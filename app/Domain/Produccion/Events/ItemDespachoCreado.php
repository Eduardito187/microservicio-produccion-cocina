<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

class ItemDespachoCreado extends BaseDomainEvent
{
    /**
     * @var string
     */
    private readonly int $etiquetaId;

    /**
     * Constructor
     * 
     * @param int|null $listaId
     * @param int $etiquetaId
     */
    public function __construct(
        int|null $listaId,
        int $etiquetaId
    ) {
        $this->etiquetaId = $etiquetaId;
        parent::__construct($listaId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lista_id' => $this->aggregateId(),
            'etiqueta_id' => $this->etiquetaId
        ];
    }
}