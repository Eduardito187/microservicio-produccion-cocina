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
    public string|int|null $id;

    /**
     * @var string
     */
    public string $sku;

    /**
     * @var string
     */
    public float $price;

    /**
     * @var string
     */
    public float $special_price;

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
