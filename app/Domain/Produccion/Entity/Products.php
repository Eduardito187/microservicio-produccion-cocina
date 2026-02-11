<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Products
 * @package App\Domain\Produccion\Entity
 */
class Products
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string
     */
    public $sku;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string
     */
    public $special_price;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string $sku
     * @param float $price
     * @param float $special_price
     */
    public function __construct(
        string|int|null $id,
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
