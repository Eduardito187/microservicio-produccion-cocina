<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarEstacionesHandler;
use App\Application\Produccion\Command\ListarEstaciones;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarEstacionesController
 * @package App\Presentation\Http\Controllers
 */
class ListarEstacionesController
{
    /**
     * @var ListarEstacionesHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarEstacionesHandler $handler
     */
    public function __construct(ListarEstacionesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarEstaciones());

        return response()->json($rows);
    }
}
