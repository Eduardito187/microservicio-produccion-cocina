<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarPaciente;
use App\Application\Produccion\Handler\EliminarPacienteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPacienteController
 */
class EliminarPacienteController
{
    /**
     * @var EliminarPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPaciente($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
