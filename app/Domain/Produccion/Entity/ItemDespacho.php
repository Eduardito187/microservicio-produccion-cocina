<?php

namespace App\Domain\Produccion\Entity;

class ItemDespacho
{
    /**
     * @var int|null
     */
    public int|null $id;

    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $productId;

    /**
     * @var int|null
     */
    public readonly int|null $paqueteId;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int $productId
     * @param int|null $paqueteId
     */
    public function __construct(
        int|null $id,
        int $ordenProduccionId,
        int $productId,
        int|null $paqueteId
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
    }
}