<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

class ProductoCreado extends BaseDomainEvent
{
    /**
     * @var string
     */
    public readonly string $sku;

    /**
     * @var string
     */
    public readonly float $price;

    /**
     * @var string
     */
    public readonly float $special_price;

    /**
     * Constructor
     * 
     * @param string $id
     * @param string $sku
     * @param string $price
     * @param string $special_price
     */
    public function __construct(
        string $id,
        string $sku,
        string $price,
        string $special_price
    ) {
        $this->sku = $sku;
        $this->price = $price;
        $this->special_price = $special_price;
        parent::__construct($id);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->aggregateId(),
            'sku' => $this->sku,
            'price' => $this->price,
            'special_price' => $this->special_price
        ];
    }
}