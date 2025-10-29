<?php

namespace App\Domain\Produccion\Entity;

class Products
{
    /**
     * @var int
     */
    public readonly int $id;

    /**
     * @var string
     */
    public readonly string $sku;

    /**
     * @var string
     */
    public readonly float $price;

    /**
     * @var string
     */
    public readonly float $special_price;

    /**
     * Constructor
     * 
     * @param int $id
     * @param string $sku
     * @param float $price
     * @param float $special_price
     */
    public function __construct(
        int $id,
        string $sku,
        float $price,
        float $special_price
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->price = $price;
        $this->special_price = $special_price;
    }
}