<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class ProductoActualizado
 */
class ProductoActualizado extends BaseDomainEvent
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

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'price' => $this->price,
            'specialPrice' => $this->specialPrice,
        ];
    }
}
