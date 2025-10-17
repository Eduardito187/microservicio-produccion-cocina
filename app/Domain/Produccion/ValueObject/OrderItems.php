<?php

namespace App\Domain\Produccion\ValueObject;

use App\Domain\Shared\ValueObject;
use DomainException;
use Traversable;

class OrderItems extends ValueObject
{
    /**
     * @var array|OrderItem[]
     */
    public readonly array $bySku;

    /**
     * Constructor
     */
    private function __construct() {
        $this->bySku = [];
    }

    /**
     * @param array $items
     * @return OrderItems
     */
    public static function fromArray(array $items): self
    {
        $self = new self();

        foreach ($items as $item) {
            $self->add($item);
        }

        $self->assertNotEmpty();

        return $self;
    }

    /**
     * @param OrderItem $item
     * @return void
     */
    public function add(OrderItem $item): void
    {
        $sku = $item->sku()->value();

        if (isset($this->bySku[$sku])) {
            $this->bySku[$sku] = $this->bySku[$sku]->merge($item);
        } else {
            $this->bySku[$sku] = $item;
        }
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        yield from array_values($this->bySku);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->bySku);
    }

    /**
     * @return int
     */
    public function totalQty(): int
    {
        $sum = 0;

        foreach ($this->bySku as $item) {
            $sum += $item->qty()->value();
        }

        return $sum;
    }

    /**
     * @throws DomainException
     * @return void
     */
    private function assertNotEmpty(): void
    {
        if ($this->count() === 0) {
            throw new DomainException('OP must contain at least one item');
        }
    }
}