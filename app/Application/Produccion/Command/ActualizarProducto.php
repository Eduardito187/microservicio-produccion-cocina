<?php

namespace App\Application\Produccion\Command;

class ActualizarProducto
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param string $sku
     * @param float $price
     * @param float $specialPrice
     */
    public function __construct(int $id, string $sku, float $price, float $specialPrice = 0.0)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
    }
}



