<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearPaciente;
use App\Application\Produccion\Handler\CrearPacienteHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearPacienteController
 */
class CrearPacienteController
{
    /**
     * @var CrearPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'documento' => ['nullable', 'string', 'max:100'],
            'suscripcionId' => ['nullable', 'uuid', 'exists:suscripcion,id'],
        ]);

        $pacienteId = $this->handler->__invoke(new CrearPaciente(
            $data['nombre'],
            $data['documento'] ?? null,
            $data['suscripcionId'] ?? null
        ));

        return response()->json(['pacienteId' => $pacienteId], 201);
    }
}
