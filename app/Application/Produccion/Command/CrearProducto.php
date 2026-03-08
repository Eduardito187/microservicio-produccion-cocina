<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearProducto
 */
class CrearProducto
{
    /**
     * @var string
     */
    public $sku;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $specialPrice;

    /**
     * Constructor
     */
    public function __construct(string $sku, float $price, float $specialPrice = 0.0)
    {
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
    }
}
