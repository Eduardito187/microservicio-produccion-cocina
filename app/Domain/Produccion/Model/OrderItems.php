<?php
namespace App\Domain\Produccion\Model;

use App\Domain\Produccion\ValueObject\OrderItem;
use InvalidArgumentException;
use DomainException;
use Traversable;

class OrderItems implements \IteratorAggregate, \Countable
{
    /** @var OrderItem[] */
    private array $bySku = [];

    /**
     * Constructor
     * 
     * @param array $bySku
     * @throws DomainException
     */
    private function __construct(array $bySku)
    {
        if (empty($bySku)) {
            throw new DomainException('La colección no puede estar vacía.');
        }

        $this->bySku = $bySku;
    }

    /**
     * @param OrderItem[] $items
     */
    public static function fromArray(array $items): self
    {
        $map = [];

        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                throw new InvalidArgumentException('Todos los elementos deben ser OrderItem');
            }

            $sku = $item->sku()->value();

            if (isset($map[$sku])) {
                $map[$sku] = $map[$sku]->merge($item);
            } else {
                $map[$sku] = $item;
            }
        }

        return new self($map);
    }

    /**
     * @param OrderItems $other
     * @return OrderItems
     */
    public function mergedWith(self $other): self
    {
        $merged = $this->bySku;

        foreach ($other->bySku as $sku => $item) {
            if (isset($merged[$sku])) {
                $merged[$sku] = $merged[$sku]->merge($item);
            } else {
                $merged[$sku] = $item;
            }
        }

        return new self($merged);
    }

    /**
     * @param OrderItem $item
     * @return OrderItems
     */
    public function withAdded(OrderItem $item): self
    {
        $copy = $this->bySku;
        $sku  = $item->sku()->value();
        $copy[$sku] = isset($copy[$sku]) ? $copy[$sku]->merge($item) : $item;

        return new self($copy);
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
}