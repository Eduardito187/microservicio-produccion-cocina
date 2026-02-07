<?php

namespace App\Domain\Produccion\Entity;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\Entity\Products;

class OrdenItem
{
    /**
     * @var string|int|null
     */
    public readonly string|int|null $id;

    /**
     * @var string|int|null
     */
    public string|int|null $ordenProduccionId;

    /**
     * @var string|int|null
     */
    public string|int|null $productId;

    /**
     * @var Qty
     */
    public readonly Qty $qty;

    /**
     * @var Sku
     */
    public readonly Sku $sku;

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
     * @param string|int|null $id
     * @param string|int|null $ordenProduccionId
     * @param string|int|null $productId
     * @param Qty $qty
     * @param Sku $sku
     * @param float $price
     * @param float $finalPrice
     */
    public function __construct(
        string|int|null $id,
        string|int|null $ordenProduccionId,
        string|int|null $productId,
        Qty $qty,
        Sku $sku,
        float $price = 0,
        float $finalPrice = 0
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->qty = $qty;
        $this->sku = $sku;
        $this->price = $price;
        $this->finalPrice = $finalPrice;
    }

    /**
     * @param Products $product
     * @return void
     */
    public function loadProduct(Products $product): void
    {
        $this->productId = $product->id;
        $this->price = $product->price;

        if ($product->special_price != 0) {
            $this->finalPrice = $product->special_price;
        }
    }

    /**
     * @return Sku
     */
    public function sku(): Sku
    {
        return $this->sku;
    }

    /**
     * @return Qty
     */
    public function qty(): Qty
    {
        return $this->qty;
    }
}