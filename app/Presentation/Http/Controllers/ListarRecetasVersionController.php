<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarRecetasVersionHandler;
use App\Application\Produccion\Command\ListarRecetasVersion;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarRecetasVersionController
 * @package App\Presentation\Http\Controllers
 */
class ListarRecetasVersionController
{
    /**
     * @var ListarRecetasVersionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarRecetasVersionHandler $handler
     */
    public function __construct(ListarRecetasVersionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarRecetasVersion());

        return response()->json($rows);
    }
}
