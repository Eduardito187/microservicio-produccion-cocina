<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarDireccionHandler;
use App\Application\Produccion\Command\ActualizarDireccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarDireccionController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarDireccionController
{
    /**
     * @var ActualizarDireccionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarDireccionHandler $handler
     */
    public function __construct(ActualizarDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:150'],
            'linea1' => ['required', 'string', 'max:255'],
            'linea2' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:150'],
            'provincia' => ['nullable', 'string', 'max:150'],
            'pais' => ['nullable', 'string', 'max:150'],
            'geo' => ['nullable', 'array'],
        ]);

        try {
            $direccionId = $this->handler->__invoke(new ActualizarDireccion(
                $id,
                $data['nombre'] ?? null,
                $data['linea1'],
                $data['linea2'] ?? null,
                $data['ciudad'] ?? null,
                $data['provincia'] ?? null,
                $data['pais'] ?? null,
                $data['geo'] ?? null
            ));

            return response()->json(['direccionId' => $direccionId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
