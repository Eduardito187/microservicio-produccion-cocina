<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarSuscripcion;
use App\Application\Produccion\Handler\ActualizarSuscripcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarSuscripcionController
 */
class ActualizarSuscripcionController
{
    /**
     * @var ActualizarSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        try {
            $suscripcionId = $this->handler->__invoke(new ActualizarSuscripcion(
                $id,
                $data['nombre']
            ));

            return response()->json(['suscripcionId' => $suscripcionId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
