<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearRecetaHandler;
use App\Application\Produccion\Command\CrearReceta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrearRecetaController
{
    private $handler;

    public function __construct(CrearRecetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'nutrientes' => ['nullable', 'array'],
            'ingredientes' => ['nullable', 'array'],
            'ingredients' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'totalCalories' => ['nullable', 'integer', 'min:0'],
        ]);

        $nombre = $data['nombre'] ?? ($data['name'] ?? null);
        if (!is_string($nombre) || trim($nombre) === '') {
            return response()->json(['message' => 'El campo nombre o name es requerido.'], 422);
        }

        $recetaId = $this->handler->__invoke(new CrearReceta(
            $nombre,
            $data['nutrientes'] ?? null,
            $data['ingredientes'] ?? ($data['ingredients'] ?? null),
            $data['description'] ?? null,
            $data['instructions'] ?? null,
            $data['totalCalories'] ?? null
        ));

        return response()->json(['recetaId' => $recetaId], 201);
    }
}
