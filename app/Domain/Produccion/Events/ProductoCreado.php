<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class ProductoCreado
 * @package App\Domain\Produccion\Events
 */
class ProductoCreado extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $specialPrice;

    /**
     * Constructor
     *
     * @param string|int|null $productoId
     * @param string $sku
     * @param float $price
     * @param float $specialPrice
     */
    public function __construct(
        string|int|null $productoId,
        string $sku,
        float $price,
        float $specialPrice
    ) {
        parent::__construct($productoId);
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'price' => $this->price,
            'specialPrice' => $this->specialPrice,
        ];
    }
}
