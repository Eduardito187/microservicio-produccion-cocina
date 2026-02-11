<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class CalendarioCrudTest
 * @package Tests\Feature\Maestros
 */
class CalendarioCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_calendario(): void
    {
        $create = $this->postJson(route('calendarios.crear'), [
            'fecha' => '2026-01-10', 'sucursalId' => 'SCZ-001'
        ]);

        $create->assertCreated()->assertJsonStructure(['calendarioId']);
        $calendarioId = $create->json('calendarioId');

        $this->getJson(route('calendarios.listar'))
            ->assertOk()->assertJsonFragment(['id' => $calendarioId, 'sucursal_id' => 'SCZ-001']);
        $this->getJson(route('calendarios.ver', ['id' => $calendarioId]))
            ->assertOk()->assertJsonFragment(['id' => $calendarioId, 'sucursal_id' => 'SCZ-001']);

        $update = $this->putJson(route('calendarios.actualizar', ['id' => $calendarioId]), [
            'fecha' => '2026-01-11', 'sucursalId' => 'SCZ-002'
        ]);

        $update->assertOk()->assertJsonPath('calendarioId', $calendarioId);
        $delete = $this->deleteJson(route('calendarios.eliminar', ['id' => $calendarioId]));
        $delete->assertNoContent();
    }
}
