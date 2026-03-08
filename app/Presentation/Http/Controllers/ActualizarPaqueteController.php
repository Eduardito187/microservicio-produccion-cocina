<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarPaquete;
use App\Application\Produccion\Handler\ActualizarPaqueteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarPaqueteController
 */
class ActualizarPaqueteController
{
    /**
     * @var ActualizarPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarPaqueteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'etiquetaId' => ['nullable', 'uuid', 'exists:etiqueta,id'],
            'ventanaId' => ['nullable', 'uuid', 'exists:ventana_entrega,id'],
            'direccionId' => ['nullable', 'uuid', 'exists:direccion,id'],
        ]);

        try {
            $paqueteId = $this->handler->__invoke(new ActualizarPaquete(
                $id,
                $data['etiquetaId'] ?? null,
                $data['ventanaId'] ?? null,
                $data['direccionId'] ?? null
            ));

            return response()->json(['paqueteId' => $paqueteId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
