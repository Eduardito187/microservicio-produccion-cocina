<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarPacienteHandler;
use App\Application\Produccion\Command\ActualizarPaciente;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarPacienteController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarPacienteController
{
    /**
     * @var ActualizarPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarPacienteHandler $handler
     */
    public function __construct(ActualizarPacienteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
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
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
