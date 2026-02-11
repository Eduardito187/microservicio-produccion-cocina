<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearEtiquetaHandler;
use App\Application\Produccion\Command\CrearEtiqueta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearEtiquetaController
 * @package App\Presentation\Http\Controllers
 */
class CrearEtiquetaController
{
    /**
     * @var CrearEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearEtiquetaHandler $handler
     */
    public function __construct(CrearEtiquetaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recetaVersionId' => ['nullable', 'uuid', 'exists:receta_version,id'],
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
            'pacienteId' => ['nullable', 'uuid', 'exists:paciente,id'],
            'qrPayload' => ['nullable', 'array'],
        ]);

        $etiquetaId = $this->handler->__invoke(new CrearEtiqueta(
            $data['recetaVersionId'] ?? null,
            $data['suscripcionId'] ?? null,
            $data['pacienteId'] ?? null,
            $data['qrPayload'] ?? null
        ));

        return response()->json(['etiquetaId' => $etiquetaId], 201);
    }
}
