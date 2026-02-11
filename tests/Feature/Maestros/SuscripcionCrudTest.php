<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class SuscripcionCrudTest
 * @package Tests\Feature\Maestros
 */
class SuscripcionCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_suscripcion(): void
    {
        $create = $this->postJson(route('suscripciones.crear'), ['nombre' => 'Suscripcion 1']);
        $create->assertCreated()->assertJsonStructure(['suscripcionId']);
        $suscripcionId = $create->json('suscripcionId');

        $this->getJson(route('suscripciones.listar'))
            ->assertOk()->assertJsonFragment(['id' => $suscripcionId, 'nombre' => 'Suscripcion 1']);
        $this->getJson(route('suscripciones.ver', ['id' => $suscripcionId]))
            ->assertOk()->assertJsonFragment(['id' => $suscripcionId, 'nombre' => 'Suscripcion 1']);
        $update = $this->putJson(route('suscripciones.actualizar', ['id' => $suscripcionId]), ['nombre' => 'Suscripcion 2']);
        $update->assertOk()->assertJsonPath('suscripcionId', $suscripcionId);

        $delete = $this->deleteJson(route('suscripciones.eliminar', ['id' => $suscripcionId]));
        $delete->assertNoContent();
    }
}
