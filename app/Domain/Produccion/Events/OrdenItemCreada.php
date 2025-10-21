<?php

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

class OrdenItemCreada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private readonly string $ordenProduccionId;

    /**
     * @var string
     */
    private readonly string $productId;

    /**
     * @var string
     */
    private readonly string $sku;

    /**
     * @var int
     */
    private readonly int $qty;

    /**
     * @var float
     */
    private readonly float $finalPrice;

    /**
     * Constructor
     * 
     * @param string $itemId
     * @param string $ordenProduccionId
     * @param string $productId
     * @param string $sku
     * @param int $qty
     * @param float $finalPrice
     */
    public function __construct(
        string $itemId,
        string $ordenProduccionId,
        string $productId,
        string $sku,
        int $qty,
        float $finalPrice
    ) {
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->sku = $sku;
        $this->qty = $qty;
        $this->finalPrice = $finalPrice;
        parent::__construct($itemId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'item_id' => $this->aggregateId(),
            'ordenProduccionId' => $this->ordenProduccionId,
            'productId' => $this->productId,
            'sku' => $this->sku,
            'qty' => $this->qty,
            'finalPrice' => $this->finalPrice
        ];
    }
}