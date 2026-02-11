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
    public function test_crear_actualizar_y_eliminar_receta_version(): void
    {
        $create = $this->postJson(route('recetas-version.crear'), [
            'nombre' => 'Receta 1',
            'nutrientes' => ['calorias' => 100],
            'ingredientes' => ['harina' => 1],
            'version' => 1
        ]);

        $create->assertCreated()->assertJsonStructure(['recetaVersionId']);
        $recetaVersionId = $create->json('recetaVersionId');
        $this->getJson(route('recetas-version.listar'))
            ->assertOk()->assertJsonFragment(['id' => $recetaVersionId, 'nombre' => 'Receta 1']);
        $this->getJson(route('recetas-version.ver', ['id' => $recetaVersionId]))
            ->assertOk()->assertJsonFragment(['id' => $recetaVersionId, 'nombre' => 'Receta 1']);

        $update = $this->putJson(route('recetas-version.actualizar', ['id' => $recetaVersionId]), [
            'nombre' => 'Receta 2',
            'nutrientes' => ['calorias' => 200],
            'ingredientes' => ['harina' => 2],
            'version' => 2
        ]);

        $update->assertOk()->assertJsonPath('recetaVersionId', $recetaVersionId);
        $delete = $this->deleteJson(route('recetas-version.eliminar', ['id' => $recetaVersionId]));
        $delete->assertNoContent();
    }
}
