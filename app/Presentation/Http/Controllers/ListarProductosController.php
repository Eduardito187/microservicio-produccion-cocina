<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarProductosHandler;
use App\Application\Produccion\Command\ListarProductos;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarProductosController
 * @package App\Presentation\Http\Controllers
 */
class ListarProductosController
{
    /**
     * @var ListarProductosHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarProductosHandler $handler
     */
    public function __construct(ListarProductosHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarProductos());

        return response()->json($rows);
    }
}
