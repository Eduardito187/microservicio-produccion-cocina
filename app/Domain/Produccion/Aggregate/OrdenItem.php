<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenItemCreada;
use App\Domain\Cocina\Aggregate\Products;
use App\Domain\Shared\AggregateRoot;
use DomainException;

class OrdenItem
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    public readonly int|null $id;

    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int|null
     */
    public int|null $productId;

    /**
     * @var string
     */
    public readonly string $sku;

    /**
     * @var int
     */
    public readonly int $qty;

    /**
     * @var float
     */
    public float $price;

    /**
     * @var float
     */
    public float $finalPrice;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int|null $productId
     * @param string $sku
     * @param int $qty
     * @param float $price
     * @param float $finalPrice
     */
    public function __construct(
        int|null $id,
        int $ordenProduccionId,
        int|null $productId,
        string $sku,
        int $qty,
        float $price,
        float $finalPrice
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->sku = $sku;
        $this->qty = $qty;
        $this->price = $price;
        $this->finalPrice = $finalPrice;
    }

    /**
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int|null $productId
     * @param string $sku
     * @param int $qty
     * @param float $price
     * @param float $finalPrice
     * @return OrdenItem
     */
    public static function crear(int|null $id, int $ordenProduccionId, int|null $productId, string $sku, int $qty, float $price, float $finalPrice): self
    {
        $self = new self(
            $id,
            $ordenProduccionId,
            $productId,
            $sku,
            $qty,
            $price,
            $finalPrice
        );

        $self->record(
            new OrdenItemCreada(
                $id,
                $ordenProduccionId,
                $productId,
                $sku,
                $qty,
                $finalPrice
            )
        );

        return $self;
    }

    /**
     * @param Products $product
     * @throws DomainException
     * @return void
     */
    public function loadProduct(Products $product): void
    {
        if ($product->id == null) {
            throw new DomainException('El producto es invalido.');
        }

        $this->productId = $product->id;
        $this->price = $product->price;

        if ($product->special_price != 0) {
            $this->finalPrice = $product->special_price;
        }
    }
}