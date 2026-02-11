<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarProducto
 * @package App\Application\Produccion\Command
 */
class ActualizarProducto
{
    /**
     * @var string
     */
    public $id;

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
     * @param string $id
     * @param string $sku
     * @param float $price
     * @param float $specialPrice
     */
    public function __construct(string $id, string $sku, float $price, float $specialPrice = 0.0)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
    }
}
