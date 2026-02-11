<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearProducto
 * @package App\Application\Produccion\Command
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
