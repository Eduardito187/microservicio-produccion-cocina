<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarRecetaHandler;
use App\Application\Produccion\Command\ActualizarReceta;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizarRecetaController
{
    private $handler;

    public function __construct(ActualizarRecetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
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
            'version' => ['nullable', 'int', 'min:1'],
        ]);

        $nombre = $data['nombre'] ?? ($data['name'] ?? null);
        if (!is_string($nombre) || trim($nombre) === '') {
            return response()->json(['message' => 'El campo nombre o name es requerido.'], 422);
        }

        try {
            $recetaId = $this->handler->__invoke(new ActualizarReceta(
                $id,
                $nombre,
                $data['nutrientes'] ?? null,
                $data['ingredientes'] ?? ($data['ingredients'] ?? null),
                $data['version'] ?? 1,
                $data['description'] ?? null,
                $data['instructions'] ?? null,
                $data['totalCalories'] ?? null
            ));

            return response()->json(['recetaId' => $recetaId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
