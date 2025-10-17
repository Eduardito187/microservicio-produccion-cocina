<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenItemCreada;
use App\Domain\Shared\AggregateRoot;

class OrdenItem
{
    use AggregateRoot;

    /**
     * @var string
     */
    public readonly string $id;

    /**
     * @var string
     */
    public readonly string $ordenProduccionId;

    /**
     * @var string
     */
    public readonly string $productId;

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
    public readonly float $price;

    /**
     * @var float
     */
    public readonly float $finalPrice;

    /**
     * Constructor
     * 
     * @param string $id
     * @param string $ordenProduccionId
     * @param string $productId
     * @param string $sku
     * @param int $qty
     * @param float $price
     * @param float $finalPrice
     */
    public function __construct(
        string $id,
        string $ordenProduccionId,
        string $productId,
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
     * @param string $id
     * @param string $ordenProduccionId
     * @param string $productId
     * @param string $sku
     * @param int $qty
     * @param float $price
     * @param float $finalPrice
     * @return OrdenItem
     */
    public static function crear(string $id, string $ordenProduccionId, string $productId, string $sku, int $qty, float $price, float $finalPrice): self
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
}
