<?php

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\Entity\Products;
use PHPUnit\Framework\TestCase;

class OrdenItemTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_load_product_setea_product_id_y_precios(): void
    {
        $item = new OrdenItem(
            id: null,
            ordenProduccionId: null,
            productId: null,
            qty: new Qty(2),
            sku: new Sku('PIZZA-PEP')
        );

        $product = new Products(
            id: 10,
            sku: 'PIZZA-PEP',
            price: 100.0,
            special_price: 0.0
        );

        $item->loadProduct($product);

        $this->assertSame(10, $item->productId);
        $this->assertSame(100.0, $item->price);
        $this->assertSame(0.0, $item->finalPrice);
    }

    /**
     * @inheritDoc
     */
    public function test_load_product_aplica_special_price_si_existe(): void
    {
        $item = new OrdenItem(
            id: null,
            ordenProduccionId: null,
            productId: null,
            qty: new Qty(1),
            sku: new Sku('PIZZA-MARG')
        );

        $product = new Products(
            id: 11,
            sku: 'PIZZA-MARG',
            price: 200.0,
            special_price: 150.0
        );

        $item->loadProduct($product);

        $this->assertSame(11, $item->productId);
        $this->assertSame(200.0, $item->price);
        $this->assertSame(150.0, $item->finalPrice);
    }
}