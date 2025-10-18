<?php

namespace App\Domain\Produccion\ValueObject;

use App\Domain\Shared\ValueObject;
use LogicException;

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
     * Constructor
     * 
     * @param Sku $sku
     * @param Qty $qty
     */
    public function __construct(Sku $sku, Qty $qty) {
        $this->sku = $sku;
        $this->qty = $qty;
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
     * @throws LogicException
     * @return OrderItem
     */
    public function merge(OrderItem $other): self
    {
        if (!$this->sameSku($other)) {
            throw new LogicException('Cannot merge items with different SKUs');
        }

        return new self($this->sku, $this->qty->add($other->qty()));
    }
}