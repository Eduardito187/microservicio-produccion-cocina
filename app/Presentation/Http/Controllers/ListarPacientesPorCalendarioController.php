<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarPacientesPorCalendario;
use App\Application\Produccion\Handler\ListarPacientesPorCalendarioHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPacientesPorCalendarioController
 */
class ListarPacientesPorCalendarioController
{
    /**
     * @var ListarPacientesPorCalendarioHandler
     */
    private $handler;

    public function __construct(ListarPacientesPorCalendarioHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarPacientesPorCalendario($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
