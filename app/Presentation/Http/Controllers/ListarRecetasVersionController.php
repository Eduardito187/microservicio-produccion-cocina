<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarRecetasVersion;
use App\Application\Produccion\Handler\ListarRecetasVersionHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarRecetasVersionController
 */
class ListarRecetasVersionController
{
    /**
     * @var ListarRecetasVersionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarRecetasVersionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarRecetasVersion);

        return response()->json($rows);
    }
}
