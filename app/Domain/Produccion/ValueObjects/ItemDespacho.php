<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;

class ItemDespacho extends ValueObject
{
    /**
     * @var int
     */
    public int $ordenProduccionId;

    /**
     * @var int
     */
    public int $productId;

    /**
     * @var int
     */
    public int|null $paqueteId;

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
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
    }
}