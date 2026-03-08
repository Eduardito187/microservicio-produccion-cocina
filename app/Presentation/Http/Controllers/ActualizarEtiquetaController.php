<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarEtiqueta;
use App\Application\Produccion\Handler\ActualizarEtiquetaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarEtiquetaController
 */
class ActualizarEtiquetaController
{
    /**
     * @var ActualizarEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarEtiquetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
            'pacienteId' => ['nullable', 'uuid', 'exists:paciente,id'],
            'qrPayload' => ['nullable', 'array'],
        ]);

        try {
            $etiquetaId = $this->handler->__invoke(new ActualizarEtiqueta(
                $id,
                $data['suscripcionId'] ?? null,
                $data['pacienteId'] ?? null,
                $data['qrPayload'] ?? null
            ));

            return response()->json(['etiquetaId' => $etiquetaId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
