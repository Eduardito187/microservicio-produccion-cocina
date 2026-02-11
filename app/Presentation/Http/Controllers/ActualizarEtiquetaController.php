<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarEtiquetaHandler;
use App\Application\Produccion\Command\ActualizarEtiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarEtiquetaController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarEtiquetaController
{
    /**
     * @var ActualizarEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarEtiquetaHandler $handler
     */
    public function __construct(ActualizarEtiquetaHandler $handler) {
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
            'recetaVersionId' => ['nullable', 'uuid', 'exists:receta_version,id'],
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
            'pacienteId' => ['nullable', 'uuid', 'exists:paciente,id'],
            'qrPayload' => ['nullable', 'array'],
        ]);

        try {
            $etiquetaId = $this->handler->__invoke(new ActualizarEtiqueta(
                $id,
                $data['recetaVersionId'] ?? null,
                $data['suscripcionId'] ?? null,
                $data['pacienteId'] ?? null,
                $data['qrPayload'] ?? null
            ));

            return response()->json(['etiquetaId' => $etiquetaId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
