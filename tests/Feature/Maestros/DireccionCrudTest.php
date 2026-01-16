<?php

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DireccionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_direccion(): void
    {
        $create = $this->postJson(route('direcciones.crear'), [
            'nombre' => 'Casa',
            'linea1' => 'Calle 1',
            'linea2' => 'Depto 2',
            'ciudad' => 'Ciudad',
            'provincia' => 'Provincia',
            'pais' => 'Pais',
            'geo' => ['lat' => 1.23, 'lng' => 4.56],
        ]);

        $create->assertCreated()->assertJsonStructure(['direccionId']);
        $direccionId = $create->json('direccionId');

        $update = $this->putJson(route('direcciones.actualizar', ['id' => $direccionId]), [
            'nombre' => 'Casa 2',
            'linea1' => 'Calle 2',
            'linea2' => 'Depto 3',
            'ciudad' => 'Ciudad 2',
            'provincia' => 'Provincia 2',
            'pais' => 'Pais 2',
            'geo' => ['lat' => 2.34, 'lng' => 5.67],
        ]);

        $update->assertOk()->assertJsonPath('direccionId', $direccionId);

        $delete = $this->deleteJson(route('direcciones.eliminar', ['id' => $direccionId]));
        $delete->assertNoContent();
    }
}
