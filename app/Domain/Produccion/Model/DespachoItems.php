<?php
namespace App\Domain\Produccion\Model;

use App\Domain\Produccion\ValueObject\ItemDespacho;
use InvalidArgumentException;
use DomainException;
use Traversable;

class DespachoItems implements \IteratorAggregate, \Countable
{
    /** @var ItemDespacho[] */
    private array $items = [];

    /**
     * Constructor
     * 
     * @param array $items
     * @throws DomainException
     */
    private function __construct(array $items)
    {
        if (empty($items)) {
            throw new DomainException('La colección no puede estar vacía.');
        }

        $this->items = $items;
    }

    /**
     * @param DespachoItems $other
     * @return DespachoItems
     */
    public function mergedWith(self $other): self
    {
        return new self(
            array_merge(
                $this->items,
                $other->items
            )
        );
    }

    /**
     * @param ItemDespacho[] $items
     */
    public static function fromArray(array $items): self
    {
        $map = [];

        foreach ($items as $item) {
            if (!$item instanceof ItemDespacho) {
                throw new InvalidArgumentException('Todos los elementos deben ser ItemDespacho');
            }

            $map[] = $item;
        }

        return new self($map);
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        yield from array_values($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}