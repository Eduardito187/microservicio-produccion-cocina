<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarCalendariosPorSuscripcion;
use App\Application\Produccion\Handler\ListarCalendariosPorSuscripcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendariosPorSuscripcionController
 */
class ListarCalendariosPorSuscripcionController
{
    /**
     * @var ListarCalendariosPorSuscripcionHandler
     */
    private $handler;

    public function __construct(ListarCalendariosPorSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarCalendariosPorSuscripcion($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
