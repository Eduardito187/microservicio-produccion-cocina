<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Entity;

use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\Products;
use PHPUnit\Framework\TestCase;

/**
 * @class EntitiesTest
 * @package Tests\Unit\Domain\Produccion\Entity
 */
class EntitiesTest extends TestCase
{
    /**
     * @return void
     */
    public function test_products_assigns_properties(): void
    {
        $product = new Products(id: 10, sku: 'SKU-1', price: 25.5, special_price: 0.0);

        $this->assertSame(10, $product->id);
        $this->assertSame('SKU-1', $product->sku);
        $this->assertSame(25.5, $product->price);
        $this->assertSame(0.0, $product->special_price);
    }

    /**
     * @return void
     */
    public function test_item_despacho_assigns_properties(): void
    {
        $itemDespacho = new ItemDespacho(id: null, ordenProduccionId: 1, productId: 10, paqueteId: null);

        $this->assertNull($itemDespacho->id);
        $this->assertSame(1, $itemDespacho->ordenProduccionId);
        $this->assertSame(10, $itemDespacho->productId);
        $this->assertNull($itemDespacho->paqueteId);

        $itemDespacho->id = 99;
        $this->assertSame(99, $itemDespacho->id);
    }
}
