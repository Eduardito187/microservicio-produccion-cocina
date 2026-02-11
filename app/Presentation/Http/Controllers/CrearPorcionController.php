<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearPorcionHandler;
use App\Application\Produccion\Command\CrearPorcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearPorcionController
 * @package App\Presentation\Http\Controllers
 */
class CrearPorcionController
{
    /**
     * @var CrearPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearPorcionHandler $handler
     */
    public function __construct(CrearPorcionHandler $handler) {
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
            'pesoGr' => ['required', 'int', 'min:1'],
        ]);

        $porcionId = $this->handler->__invoke(new CrearPorcion(
            $data['nombre'],
            $data['pesoGr']
        ));

        return response()->json(['porcionId' => $porcionId], 201);
    }
}
