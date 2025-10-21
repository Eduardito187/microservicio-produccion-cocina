<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;
use Traversable;

class ItemDespachos extends ValueObject
{
    /**
     * @var array|ItemDespacho[]
     */
    public readonly array $items;

    /**
     * Constructor
     */
    private function __construct() {
        $this->items = [];
    }

    /**
     * @param array $items
     * @return ItemDespachos
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
     * @param ItemDespacho $item
     * @return void
     */
    public function add(ItemDespacho $item): void
    {
        $this->items[] = $item;
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

    /**
     * @throws DomainException
     * @return void
     */
    private function assertNotEmpty(): void
    {
        if ($this->count() === 0) {
            throw new DomainException('Despachos insuficientes.');
        }
    }
}