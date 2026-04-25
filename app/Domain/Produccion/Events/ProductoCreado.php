<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class ProductoCreado
 */
class ProductoCreado extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var ?string
     */
    private $nombre;

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
        float $specialPrice,
        ?string $nombre = null
    ) {
        parent::__construct($productoId);
        $this->sku = $sku;
        $this->price = $price;
        $this->specialPrice = $specialPrice;
        $this->nombre = $nombre;
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'price' => $this->price,
            'specialPrice' => $this->specialPrice,
        ];
    }
}
