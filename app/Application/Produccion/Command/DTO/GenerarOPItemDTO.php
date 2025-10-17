<?php

namespace App\Application\Produccion\Command\DTO;

class GenerarOPItemDTO
{
    /**
     * @var string
     */
    public readonly string $sku;

    /**
     * @var int
     */
    public readonly int $qty;

    /**
     * Constructor
     * 
     * @param string $sku
     * @param int $qty
     */
    public function __construct(string $sku, int $qty)
    {
        $this->sku = $sku;
        $this->qty = $qty;
    }
}