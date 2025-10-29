<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

class ItemDespachoCreado extends BaseDomainEvent
{
    /**
     * @var int
     */
    private readonly int $productId;

    /**
     * @var int|null
     */
    private readonly int|null $paqueteId;

    /**
     * Constructor
     * 
     * @param int $ordenProduccionId
     * @param int $productId
     * @param int|null $paqueteId
     */
    public function __construct(
        int $ordenProduccionId,
        int $productId,
        int|null $paqueteId
    ) {
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
        parent::__construct($ordenProduccionId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lista_id' => $this->aggregateId(),
            'product_id' => $this->productId,
            'paquete_id' => $this->paqueteId
        ];
    }
}