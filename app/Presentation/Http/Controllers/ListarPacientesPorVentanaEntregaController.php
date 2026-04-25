<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarPacientesPorVentanaEntrega;
use App\Application\Produccion\Handler\ListarPacientesPorVentanaEntregaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPacientesPorVentanaEntregaController
 */
class ListarPacientesPorVentanaEntregaController
{
    /**
     * @var ListarPacientesPorVentanaEntregaHandler
     */
    private $handler;

    public function __construct(ListarPacientesPorVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarPacientesPorVentanaEntrega($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
