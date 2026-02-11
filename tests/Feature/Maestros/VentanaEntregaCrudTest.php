<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class VentanaEntregaCrudTest
 * @package Tests\Feature\Maestros
 */
class VentanaEntregaCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_ventana_entrega(): void
    {
        $create = $this->postJson(route('ventanas-entrega.crear'), [
            'desde' => '2026-01-01 08:00:00',
            'hasta' => '2026-01-01 12:00:00',
        ]);

        $create->assertCreated()->assertJsonStructure(['ventanaEntregaId']);
        $ventanaId = $create->json('ventanaEntregaId');

        $this->getJson(route('ventanas-entrega.listar'))->assertOk()->assertJsonFragment(['id' => $ventanaId]);

        $this->getJson(route('ventanas-entrega.ver', ['id' => $ventanaId]))
            ->assertOk()->assertJsonFragment(['id' => $ventanaId]);

        $update = $this->putJson(route('ventanas-entrega.actualizar', ['id' => $ventanaId]), [
            'desde' => '2026-01-01 09:00:00', 'hasta' => '2026-01-01 13:00:00'
        ]);

        $update->assertOk()->assertJsonPath('ventanaEntregaId', $ventanaId);

        $delete = $this->deleteJson(route('ventanas-entrega.eliminar', ['id' => $ventanaId]));
        $delete->assertNoContent();
    }
}
