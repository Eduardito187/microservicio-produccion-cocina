<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class PlanificarOPValidationTest
 * @package Tests\Feature\Produccion
 */
class PlanificarOPValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_planificar_requiere_existencia_fk(): void
    {
        $payload = [
            'ordenProduccionId' => (string) Str::uuid(),
            'estacionId' => (string) Str::uuid(),
            'recetaVersionId' => (string) Str::uuid(),
            'porcionId' => (string) Str::uuid(),
        ];

        $this->postJson(route('produccion.ordenes.planificar'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'ordenProduccionId',
                'estacionId',
                'recetaVersionId',
                'porcionId',
            ]);
    }
}
