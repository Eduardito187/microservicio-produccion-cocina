<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarCalendariosPorPaciente;
use App\Application\Produccion\Handler\ListarCalendariosPorPacienteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendariosPorPacienteController
 */
class ListarCalendariosPorPacienteController
{
    /**
     * @var ListarCalendariosPorPacienteHandler
     */
    private $handler;

    public function __construct(ListarCalendariosPorPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarCalendariosPorPaciente($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
