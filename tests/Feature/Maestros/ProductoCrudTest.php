<?php

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_producto(): void
    {
        $create = $this->postJson(route('productos.crear'), [
            'sku' => 'SKU-001',
            'price' => 10.5,
            'specialPrice' => 9.5,
        ]);

        $create->assertCreated()->assertJsonStructure(['productId']);
        $productId = $create->json('productId');

        $this->getJson(route('productos.listar'))
            ->assertOk()
            ->assertJsonFragment(['id' => $productId, 'sku' => 'SKU-001']);

        $this->getJson(route('productos.ver', ['id' => $productId]))
            ->assertOk()
            ->assertJsonFragment(['id' => $productId, 'sku' => 'SKU-001']);

        $update = $this->putJson(route('productos.actualizar', ['id' => $productId]), [
            'sku' => 'SKU-002',
            'price' => 12.5,
            'specialPrice' => 10.0,
        ]);

        $update->assertOk()->assertJsonPath('productId', $productId);

        $delete = $this->deleteJson(route('productos.eliminar', ['id' => $productId]));
        $delete->assertNoContent();
    }
}
