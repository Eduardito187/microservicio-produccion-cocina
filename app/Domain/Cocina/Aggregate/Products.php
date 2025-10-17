<?php

namespace App\Domain\Cocina\Aggregate;

use App\Domain\Produccion\Events\ProductoCreado;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;

class Products
{
    use AggregateRoot;

    /**
     * @var string
     */
    public readonly string $id;

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
     * @param float $price
     * @param float $special_price
     */
    public function __construct(
        string $id,
        string $sku,
        float $price,
        float $special_price
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->price = $price;
        $this->special_price = $special_price;
    }

    /**
     * @param string $id
     * @param string $sku
     * @param float $price
     * @param float $special_price
     * @return Products
     */
    public static function crear(string $id, string $sku, float $price, float $special_price): self
    {
        $self = new self(
            $id, 
            $sku, 
            $price,
            $special_price
        );

        $self->record(
            new ProductoCreado(
                $id, 
                $sku, 
                $price,
                $special_price
            )
        );

        return $self;
    }
}