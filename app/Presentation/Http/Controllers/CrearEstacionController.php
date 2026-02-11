<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearEstacionHandler;
use App\Application\Produccion\Command\CrearEstacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearEstacionController
 * @package App\Presentation\Http\Controllers
 */
class CrearEstacionController
{
    /**
     * @var CrearEstacionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearEstacionHandler $handler
     */
    public function __construct(CrearEstacionHandler $handler) {
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
            'capacidad' => ['nullable', 'int'],
        ]);

        $estacionId = $this->handler->__invoke(new CrearEstacion(
            $data['nombre'],
            $data['capacidad'] ?? null
        ));

        return response()->json(['estacionId' => $estacionId], 201);
    }
}
