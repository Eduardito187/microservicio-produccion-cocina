<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerPaciente;
use App\Application\Produccion\Handler\VerPacienteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerPacienteController
 */
class VerPacienteController
{
    /**
     * @var VerPacienteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPaciente($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
