<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarPaciente;
use App\Application\Produccion\Handler\ActualizarPacienteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarPacienteController
 */
class ActualizarPacienteController
{
    /**
     * @var ActualizarPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'documento' => ['nullable', 'string', 'max:100'],
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
        ]);

        try {
            $pacienteId = $this->handler->__invoke(new ActualizarPaciente(
                $id,
                $data['nombre'],
                $data['documento'] ?? null,
                $data['suscripcionId'] ?? null
            ));

            return response()->json(['pacienteId' => $pacienteId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
