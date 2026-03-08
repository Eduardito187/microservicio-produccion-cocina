<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;

/**
 * @class OrdenItem
 */
class OrdenItem
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|int|null
     */
    public $ordenProduccionId;

    /**
     * @var string|int|null
     */
    public $productId;

    /**
     * @var Qty
     */
    public $qty;

    /**
     * @var Sku
     */
    public $sku;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $finalPrice;

    /**
     * Constructor
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

    public function loadProduct(Products $product): void
    {
        $this->productId = $product->id;
        $this->price = $product->price;

        if ($product->special_price != 0) {
            $this->finalPrice = $product->special_price;
        }
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function qty(): Qty
    {
        return $this->qty;
    }
}
