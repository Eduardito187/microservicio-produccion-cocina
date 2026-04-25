<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarVentanasEntregaPorPaciente;
use App\Application\Produccion\Handler\ListarVentanasEntregaPorPacienteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarVentanasEntregaPorPacienteController
 */
class ListarVentanasEntregaPorPacienteController
{
    /**
     * @var ListarVentanasEntregaPorPacienteHandler
     */
    private $handler;

    public function __construct(ListarVentanasEntregaPorPacienteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarVentanasEntregaPorPaciente($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
