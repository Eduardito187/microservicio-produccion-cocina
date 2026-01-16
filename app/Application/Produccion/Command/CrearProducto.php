<?php

namespace App\Application\Produccion\Command;

class CrearProducto
{
    /**
     * @var string
     */
    public string $sku;

    /**
     * @var float
     */
    public float $price;

    /**
     * @var float
     */
    public float $specialPrice;

    /**
     * Constructor
     *
     * @param string $sku
     * @param float $price
     * @param float $specialPrice
     */
    public function __construct(string $sku, float $price, float $specialPrice = 0.0)
    {
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
    }
}
