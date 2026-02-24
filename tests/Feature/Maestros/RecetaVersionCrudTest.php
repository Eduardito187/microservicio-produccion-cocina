<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class RecetaVersionCrudTest
 * @package Tests\Feature\Maestros
 */
class RecetaVersionCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_receta(): void
    {
        $create = $this->postJson(route('recetas.crear'), [
            'nombre' => 'Receta 1',
            'nutrientes' => ['calorias' => 100],
            'ingredientes' => ['harina' => 1],
            'version' => 1
        ]);

        $create->assertCreated()->assertJsonStructure(['recetaId']);
        $recetaId = $create->json('recetaId');
        $this->getJson(route('recetas.listar'))
            ->assertOk()->assertJsonFragment(['id' => $recetaId, 'nombre' => 'Receta 1']);
        $this->getJson(route('recetas.ver', ['id' => $recetaId]))
            ->assertOk()->assertJsonFragment(['id' => $recetaId, 'nombre' => 'Receta 1']);

        $update = $this->putJson(route('recetas.actualizar', ['id' => $recetaId]), [
            'nombre' => 'Receta 2',
            'nutrientes' => ['calorias' => 200],
            'ingredientes' => ['harina' => 2],
            'version' => 2
        ]);

        $update->assertOk()->assertJsonPath('recetaId', $recetaId);
        $delete = $this->deleteJson(route('recetas.eliminar', ['id' => $recetaId]));
        $delete->assertNoContent();
    }
}
