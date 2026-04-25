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
     * @var ?string
     */
    public $nombre;

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
    public function __construct(string $sku, float $price, float $specialPrice = 0.0, ?string $nombre = null)
    {
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
        $this->nombre = $nombre;
    }
}
