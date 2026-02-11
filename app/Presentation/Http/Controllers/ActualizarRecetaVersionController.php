<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarRecetaVersionHandler;
use App\Application\Produccion\Command\ActualizarRecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarRecetaVersionController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarRecetaVersionController
{
    /**
     * @var ActualizarRecetaVersionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarRecetaVersionHandler $handler
     */
    public function __construct(ActualizarRecetaVersionHandler $handler) {
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
            'nutrientes' => ['nullable', 'array'],
            'ingredientes' => ['nullable', 'array'],
            'version' => ['nullable', 'int', 'min:1'],
        ]);

        try {
            $recetaVersionId = $this->handler->__invoke(new ActualizarRecetaVersion(
                $id,
                $data['nombre'],
                $data['nutrientes'] ?? null,
                $data['ingredientes'] ?? null,
                $data['version'] ?? 1
            ));

            return response()->json(['recetaVersionId' => $recetaVersionId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
