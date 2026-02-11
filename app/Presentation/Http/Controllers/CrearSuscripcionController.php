<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearSuscripcionHandler;
use App\Application\Produccion\Command\CrearSuscripcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearSuscripcionController
 * @package App\Presentation\Http\Controllers
 */
class CrearSuscripcionController
{
    /**
     * @var CrearSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearSuscripcionHandler $handler
     */
    public function __construct(CrearSuscripcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        $suscripcionId = $this->handler->__invoke(new CrearSuscripcion(
            $data['nombre']
        ));

        return response()->json(['suscripcionId' => $suscripcionId], 201);
    }
}
