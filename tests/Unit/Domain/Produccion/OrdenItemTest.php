<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\Entity\Products;
use PHPUnit\Framework\TestCase;

/**
 * @class OrdenItemTest
 * @package Tests\Unit\Domain\Produccion
 */
class OrdenItemTest extends TestCase
{
    /**
     * @return void
     */
    public function test_load_product_setea_product_id_y_precios(): void
    {
        $item = new OrdenItem(null, null, null, new Qty(2), new Sku('PIZZA-PEP'));
        $product = new Products(10, 'PIZZA-PEP', 100.0, 0.0);
        $item->loadProduct($product);

        $this->assertSame(10, $item->productId);
        $this->assertSame(100.0, $item->price);
        $this->assertSame(0.0, $item->finalPrice);
    }

    /**
     * @return void
     */
    public function test_load_product_aplica_special_price_si_existe(): void
    {
        $item = new OrdenItem(null, null, null, new Qty(1), new Sku('PIZZA-MARG'));
        $product = new Products(11, 'PIZZA-MARG', 200.0, 150.0);

        $item->loadProduct($product);
        $this->assertSame(11, $item->productId);
        $this->assertSame(200.0, $item->price);
        $this->assertSame(150.0, $item->finalPrice);
    }
}
