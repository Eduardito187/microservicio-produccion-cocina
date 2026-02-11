<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearDireccionHandler;
use App\Application\Produccion\Command\CrearDireccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearDireccionController
 * @package App\Presentation\Http\Controllers
 */
class CrearDireccionController
{
    /**
     * @var CrearDireccionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearDireccionHandler $handler
     */
    public function __construct(CrearDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:150'],
            'linea1' => ['required', 'string', 'max:255'],
            'linea2' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:150'],
            'provincia' => ['nullable', 'string', 'max:150'],
            'pais' => ['nullable', 'string', 'max:150'],
            'geo' => ['nullable', 'array'],
        ]);

        $direccionId = $this->handler->__invoke(new CrearDireccion(
            $data['nombre'] ?? null,
            $data['linea1'],
            $data['linea2'] ?? null,
            $data['ciudad'] ?? null,
            $data['provincia'] ?? null,
            $data['pais'] ?? null,
            $data['geo'] ?? null
        ));

        return response()->json(['direccionId' => $direccionId], 201);
    }
}
