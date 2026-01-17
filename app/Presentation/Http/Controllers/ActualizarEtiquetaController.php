<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarEtiquetaHandler;
use App\Application\Produccion\Command\ActualizarEtiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizarEtiquetaController
{
    /**
     * @var ActualizarEtiquetaHandler
     */
    private ActualizarEtiquetaHandler $handler;

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
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'recetaVersionId' => ['nullable', 'int', 'exists:receta_version,id'],
            'suscripcionId' => ['nullable', 'int', 'exists:suscripcion,id'],
            'pacienteId' => ['nullable', 'int', 'exists:paciente,id'],
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



