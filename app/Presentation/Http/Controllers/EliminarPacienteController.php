<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarPacienteHandler;
use App\Application\Produccion\Command\EliminarPaciente;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPacienteController
 * @package App\Presentation\Http\Controllers
 */
class EliminarPacienteController
{
    /**
     * @var EliminarPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarPacienteHandler $handler
     */
    public function __construct(EliminarPacienteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPaciente($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
