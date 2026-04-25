<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarCalendariosPorVentanaEntrega;
use App\Application\Produccion\Handler\ListarCalendariosPorVentanaEntregaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendariosPorVentanaEntregaController
 */
class ListarCalendariosPorVentanaEntregaController
{
    /**
     * @var ListarCalendariosPorVentanaEntregaHandler
     */
    private $handler;

    public function __construct(ListarCalendariosPorVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarCalendariosPorVentanaEntrega($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
