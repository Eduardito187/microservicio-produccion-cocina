<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

class OrderItem extends ValueObject
{
    /**
     * @var Sku
     */
    public readonly Sku $sku;

    /**
     * @var Qty
     */
    public Qty $qty;

    /**
     * @var int|null
     */
    public readonly int|null $productId;

    /**
     * Constructor
     * 
     * @param Sku $sku
     * @param Qty $qty
     * @param int|null $productId
     */
    public function __construct(
        Sku $sku,
        Qty $qty,
        int|null $productId = null
    ) {
        $this->sku = $sku;
        $this->qty = $qty;
        $this->productId = $productId;
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

    /**
     * @param OrderItem $other
     * @return bool
     */
    public function sameSku(OrderItem $other): bool
    {
        return $this->sku->value() === $other->sku()->value();
    }

    /**
     * @param OrderItem $other
     * @throws DomainException
     * @return OrderItem
     */
    public function merge(OrderItem $other): self
    {
        if (!$this->sameSku($other)) {
            throw new DomainException('Cannot merge items with different SKUs');
        }

        return new self($this->sku, $this->qty->add($other->qty()));
    }
}