<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarOrdenesPorSuscripcion;
use App\Application\Produccion\Handler\ListarOrdenesPorSuscripcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ListarOrdenesPorSuscripcionController
 */
class ListarOrdenesPorSuscripcionController
{
    /**
     * @var ListarOrdenesPorSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarOrdenesPorSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json(
                $this->handler->__invoke(new ListarOrdenesPorSuscripcion($id))
            );
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
