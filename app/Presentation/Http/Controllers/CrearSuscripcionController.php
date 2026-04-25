<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearSuscripcion;
use App\Application\Produccion\Handler\CrearSuscripcionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearSuscripcionController
 */
class CrearSuscripcionController
{
    /**
     * @var CrearSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'uuid'],
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        $suscripcionId = $this->handler->__invoke(new CrearSuscripcion(
            $data['id'],
            $data['nombre']
        ));

        return response()->json(['suscripcionId' => $suscripcionId], 201);
    }
}
