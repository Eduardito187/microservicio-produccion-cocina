<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearPaqueteHandler;
use App\Application\Produccion\Command\CrearPaquete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearPaqueteController
 * @package App\Presentation\Http\Controllers
 */
class CrearPaqueteController
{
    /**
     * @var CrearPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearPaqueteHandler $handler
     */
    public function __construct(CrearPaqueteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'etiquetaId' => ['nullable', 'uuid', 'exists:etiqueta,id'],
            'ventanaId' => ['nullable', 'uuid', 'exists:ventana_entrega,id'],
            'direccionId' => ['nullable', 'uuid', 'exists:direccion,id'],
        ]);

        $paqueteId = $this->handler->__invoke(new CrearPaquete(
            $data['etiquetaId'] ?? null,
            $data['ventanaId'] ?? null,
            $data['direccionId'] ?? null
        ));

        return response()->json(['paqueteId' => $paqueteId], 201);
    }
}
