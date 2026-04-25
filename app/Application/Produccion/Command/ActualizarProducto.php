<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarProducto
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
    public function __construct(string $id, string $sku, float $price, float $specialPrice = 0.0, ?string $nombre = null)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
        $this->nombre = $nombre;
    }
}
