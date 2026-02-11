<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarPorcionHandler;
use App\Application\Produccion\Command\ActualizarPorcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarPorcionController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarPorcionController
{
    /**
     * @var ActualizarPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarPorcionHandler $handler
     */
    public function __construct(ActualizarPorcionHandler $handler) {
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
            'nombre' => ['required', 'string', 'max:150'],
            'pesoGr' => ['required', 'int', 'min:1'],
        ]);

        try {
            $porcionId = $this->handler->__invoke(new ActualizarPorcion(
                $id,
                $data['nombre'],
                $data['pesoGr']
            ));

            return response()->json(['porcionId' => $porcionId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
