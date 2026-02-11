<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearPacienteHandler;
use App\Application\Produccion\Command\CrearPaciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearPacienteController
 * @package App\Presentation\Http\Controllers
 */
class CrearPacienteController
{
    /**
     * @var CrearPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearPacienteHandler $handler
     */
    public function __construct(CrearPacienteHandler $handler) {
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
