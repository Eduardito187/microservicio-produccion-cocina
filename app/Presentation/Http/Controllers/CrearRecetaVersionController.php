<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearRecetaVersionHandler;
use App\Application\Produccion\Command\CrearRecetaVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearRecetaVersionController
 * @package App\Presentation\Http\Controllers
 */
class CrearRecetaVersionController
{
    /**
     * @var CrearRecetaVersionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearRecetaVersionHandler $handler
     */
    public function __construct(CrearRecetaVersionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
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

        $recetaId = $this->handler->__invoke(new CrearRecetaVersion(
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
