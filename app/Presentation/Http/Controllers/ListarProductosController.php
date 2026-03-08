<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarProductos;
use App\Application\Produccion\Handler\ListarProductosHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarProductosController
 */
class ListarProductosController
{
    /**
     * @var ListarProductosHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarProductosHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarProductos);

        return response()->json($rows);
    }
}
