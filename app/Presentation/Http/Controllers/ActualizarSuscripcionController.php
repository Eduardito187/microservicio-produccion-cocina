<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarSuscripcionHandler;
use App\Application\Produccion\Command\ActualizarSuscripcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizarSuscripcionController
{
    /**
     * @var ActualizarSuscripcionHandler
     */
    private ActualizarSuscripcionHandler $handler;

    /**
     * Constructor
     *
     * @param ActualizarSuscripcionHandler $handler
     */
    public function __construct(ActualizarSuscripcionHandler $handler) {
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
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        try {
            $suscripcionId = $this->handler->__invoke(new ActualizarSuscripcion(
                $id,
                $data['nombre']
            ));

            return response()->json(['suscripcionId' => $suscripcionId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
