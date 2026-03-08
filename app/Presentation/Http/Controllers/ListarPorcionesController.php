<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarPorciones;
use App\Application\Produccion\Handler\ListarPorcionesHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPorcionesController
 */
class ListarPorcionesController
{
    /**
     * @var ListarPorcionesHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarPorcionesHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPorciones);

        return response()->json($rows);
    }
}
