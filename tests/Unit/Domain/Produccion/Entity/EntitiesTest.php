<?php

namespace Tests\Unit\Domain\Produccion\Entity;

use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\Products;
use PHPUnit\Framework\TestCase;

class EntitiesTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_products_assigns_properties(): void
    {
        $p = new Products(id: 10, sku: 'SKU-1', price: 25.5, special_price: 0.0);

        $this->assertSame(10, $p->id);
        $this->assertSame('SKU-1', $p->sku);
        $this->assertSame(25.5, $p->price);
        $this->assertSame(0.0, $p->special_price);
    }

    /**
     * @inheritDoc
     */
    public function test_item_despacho_assigns_properties(): void
    {
        $i = new ItemDespacho(id: null, ordenProduccionId: 1, productId: 10, paqueteId: null);

        $this->assertNull($i->id);
        $this->assertSame(1, $i->ordenProduccionId);
        $this->assertSame(10, $i->productId);
        $this->assertNull($i->paqueteId);

        $i->id = 99;
        $this->assertSame(99, $i->id);
    }
}
