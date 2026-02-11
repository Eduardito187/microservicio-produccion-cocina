<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class ProductoCrudTest
 * @package Tests\Feature\Maestros
 */
class ProductoCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_producto(): void
    {
        $create = $this->postJson(route('productos.crear'), [
            'sku' => 'PIZZA-PEP', 'price' => 10.5, 'specialPrice' => 9.5
        ]);

        $create->assertCreated()->assertJsonStructure(['productId']);
        $productId = $create->json('productId');

        $this->getJson(route('productos.listar'))
            ->assertOk()->assertJsonFragment(['id' => $productId, 'sku' => 'PIZZA-PEP']);

        $this->getJson(route('productos.ver', ['id' => $productId]))
            ->assertOk()->assertJsonFragment(['id' => $productId, 'sku' => 'PIZZA-PEP']);

        $update = $this->putJson(route('productos.actualizar', ['id' => $productId]), [
            'sku' => 'PIZZA-MARG', 'price' => 12.5, 'specialPrice' => 10.0
        ]);

        $update->assertOk()->assertJsonPath('productId', $productId);
        $delete = $this->deleteJson(route('productos.eliminar', ['id' => $productId]));
        $delete->assertNoContent();
    }
}
