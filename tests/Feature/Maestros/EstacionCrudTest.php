<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class EstacionCrudTest
 * @package Tests\Feature\Maestros
 */
class EstacionCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_estacion(): void
    {
        $create = $this->postJson(route('estaciones.crear'), [
            'nombre' => 'Estacion 1', 'capacidad' => 10
        ]);

        $create->assertCreated()->assertJsonStructure(['estacionId']);
        $estacionId = $create->json('estacionId');

        $this->getJson(route('estaciones.listar'))
            ->assertOk()->assertJsonFragment(['id' => $estacionId, 'nombre' => 'Estacion 1']);
        $this->getJson(route('estaciones.ver', ['id' => $estacionId]))
            ->assertOk()->assertJsonFragment(['id' => $estacionId, 'nombre' => 'Estacion 1']);

        $update = $this->putJson(route('estaciones.actualizar', ['id' => $estacionId]), [
            'nombre' => 'Estacion 2', 'capacidad' => 20
        ]);

        $update->assertOk()->assertJsonPath('estacionId', $estacionId);
        $delete = $this->deleteJson(route('estaciones.eliminar', ['id' => $estacionId]));
        $delete->assertNoContent();
    }
}
