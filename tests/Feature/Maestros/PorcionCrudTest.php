<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class PorcionCrudTest
 * @package Tests\Feature\Maestros
 */
class PorcionCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_porcion(): void
    {
        $create = $this->postJson(route('porciones.crear'), [
            'nombre' => 'Porcion 1',
            'pesoGr' => 50,
        ]);

        $create->assertCreated()->assertJsonStructure(['porcionId']);
        $porcionId = $create->json('porcionId');

        $this->getJson(route('porciones.listar'))
            ->assertOk()
            ->assertJsonFragment(['id' => $porcionId, 'nombre' => 'Porcion 1']);

        $this->getJson(route('porciones.ver', ['id' => $porcionId]))
            ->assertOk()
            ->assertJsonFragment(['id' => $porcionId, 'nombre' => 'Porcion 1']);

        $update = $this->putJson(route('porciones.actualizar', ['id' => $porcionId]), [
            'nombre' => 'Porcion 2',
            'pesoGr' => 60,
        ]);

        $update->assertOk()->assertJsonPath('porcionId', $porcionId);

        $delete = $this->deleteJson(route('porciones.eliminar', ['id' => $porcionId]));
        $delete->assertNoContent();
    }
}
