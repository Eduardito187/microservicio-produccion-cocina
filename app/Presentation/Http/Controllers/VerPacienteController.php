<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerPacienteHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPaciente;
use Illuminate\Http\JsonResponse;

/**
 * @class VerPacienteController
 * @package App\Presentation\Http\Controllers
 */
class VerPacienteController
{
    /**
     * @var VerPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerPacienteHandler $handler
     */
    public function __construct(VerPacienteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPaciente($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
