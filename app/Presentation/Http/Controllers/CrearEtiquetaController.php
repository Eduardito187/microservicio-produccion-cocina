<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearEtiqueta;
use App\Application\Produccion\Handler\CrearEtiquetaHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearEtiquetaController
 */
class CrearEtiquetaController
{
    /**
     * @var CrearEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearEtiquetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
            'pacienteId' => ['nullable', 'uuid', 'exists:paciente,id'],
            'qrPayload' => ['nullable', 'array'],
        ]);

        $etiquetaId = $this->handler->__invoke(new CrearEtiqueta(
            $data['suscripcionId'] ?? null,
            $data['pacienteId'] ?? null,
            $data['qrPayload'] ?? null
        ));

        return response()->json(['etiquetaId' => $etiquetaId], 201);
    }
}
