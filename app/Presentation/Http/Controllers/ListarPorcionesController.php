<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarPorcionesHandler;
use App\Application\Produccion\Command\ListarPorciones;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPorcionesController
 * @package App\Presentation\Http\Controllers
 */
class ListarPorcionesController
{
    /**
     * @var ListarPorcionesHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarPorcionesHandler $handler
     */
    public function __construct(ListarPorcionesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPorciones());

        return response()->json($rows);
    }
}
