<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearPorcion;
use App\Application\Produccion\Handler\CrearPorcionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearPorcionController
 */
class CrearPorcionController
{
    /**
     * @var CrearPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearPorcionHandler $handler)
    {
        $this->handler = $handler;
    }

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
