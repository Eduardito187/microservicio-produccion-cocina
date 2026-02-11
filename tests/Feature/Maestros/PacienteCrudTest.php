<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class PacienteCrudTest
 * @package Tests\Feature\Maestros
 */
class PacienteCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_paciente(): void
    {
        $create = $this->postJson(route('pacientes.crear'), [
            'nombre' => 'Paciente Demo', 'documento' => 'DOC-1'
        ]);

        $create->assertCreated()->assertJsonStructure(['pacienteId']);
        $pacienteId = $create->json('pacienteId');

        $this->getJson(route('pacientes.listar'))
            ->assertOk()->assertJsonFragment(['id' => $pacienteId, 'nombre' => 'Paciente Demo']);
        $this->getJson(route('pacientes.ver', ['id' => $pacienteId]))
            ->assertOk()->assertJsonFragment(['id' => $pacienteId, 'nombre' => 'Paciente Demo']);

        $update = $this->putJson(route('pacientes.actualizar', ['id' => $pacienteId]), [
            'nombre' => 'Paciente Demo 2', 'documento' => 'DOC-2'
        ]);

        $update->assertOk()->assertJsonPath('pacienteId', $pacienteId);

        $delete = $this->deleteJson(route('pacientes.eliminar', ['id' => $pacienteId]));
        $delete->assertNoContent();
    }
}
