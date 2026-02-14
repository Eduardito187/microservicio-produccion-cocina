<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\Events\RecetaActualizadaEvent;
use PHPUnit\Framework\TestCase;

/**
 * @class RecetaActualizadaEventTest
 * @package Tests\Unit\Application\Integration
 */
class RecetaActualizadaEventTest extends TestCase
{
    /**
     * @return void
     */
    public function test_from_payload_with_new_recipe_contract(): void
    {
        $event = RecetaActualizadaEvent::fromPayload([
            'id' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            'name' => 'Ensalada Proteica',
            'description' => 'Receta alta en proteina',
            'instructions' => 'Mezclar y servir',
            'totalCalories' => 420,
            'ingredients' => [
                [
                    'idIngredient' => 'f9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
                    'quantity' => 150,
                ],
            ],
        ]);

        $this->assertSame('d9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b', $event->id);
        $this->assertSame('Ensalada Proteica', $event->nombre);
        $this->assertSame('Receta alta en proteina', $event->description);
        $this->assertSame('Mezclar y servir', $event->instructions);
        $this->assertSame(420, $event->totalCalories);
        $this->assertSame([
            [
                'idIngredient' => 'f9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
                'quantity' => 150,
            ],
        ], $event->ingredientes);
        $this->assertSame(['kcal' => 420], $event->nutrientes);
    }
}
