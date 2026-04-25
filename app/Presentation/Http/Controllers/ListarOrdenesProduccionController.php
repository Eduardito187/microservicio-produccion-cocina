<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarOrdenesProduccion;
use App\Application\Produccion\Handler\ListarOrdenesProduccionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ListarOrdenesProduccionController
 */
class ListarOrdenesProduccionController
{
    /**
     * @var ListarOrdenesProduccionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarOrdenesProduccionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->handler->__invoke(new ListarOrdenesProduccion));
    }
}
