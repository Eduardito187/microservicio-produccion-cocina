<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarEstacionHandler;
use App\Application\Produccion\Command\ActualizarEstacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizarEstacionController
{
    /**
     * @var ActualizarEstacionHandler
     */
    private ActualizarEstacionHandler $handler;

    /**
     * Constructor
     *
     * @param ActualizarEstacionHandler $handler
     */
    public function __construct(ActualizarEstacionHandler $handler) {
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
            'capacidad' => ['nullable', 'int'],
        ]);

        try {
            $estacionId = $this->handler->__invoke(new ActualizarEstacion(
                $id,
                $data['nombre'],
                $data['capacidad'] ?? null
            ));

            return response()->json(['estacionId' => $estacionId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



