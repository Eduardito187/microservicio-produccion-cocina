<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class VentanaEntregaCrudTest
 */
class VentanaEntregaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_ventana_entrega(): void
    {
        $create = $this->postJson(route('ventanas-entrega.crear'), [
            'desde' => now()->addMonth()->format('Y-m-d') . ' 08:00:00',
            'hasta' => now()->addMonth()->format('Y-m-d') . ' 12:00:00',
        ]);

        $create->assertCreated()->assertJsonStructure(['ventanaEntregaId']);
        $ventanaId = $create->json('ventanaEntregaId');

        $this->getJson(route('ventanas-entrega.listar'))->assertOk()->assertJsonFragment(['id' => $ventanaId]);

        $this->getJson(route('ventanas-entrega.ver', ['id' => $ventanaId]))
            ->assertOk()->assertJsonFragment(['id' => $ventanaId]);

        $update = $this->putJson(route('ventanas-entrega.actualizar', ['id' => $ventanaId]), [
            'desde' => now()->addMonth()->format('Y-m-d') . ' 09:00:00',
            'hasta' => now()->addMonth()->format('Y-m-d') . ' 13:00:00',
        ]);

        $update->assertOk()->assertJsonPath('ventanaEntregaId', $ventanaId);

        $delete = $this->deleteJson(route('ventanas-entrega.eliminar', ['id' => $ventanaId]));
        $delete->assertNoContent();
    }
}
