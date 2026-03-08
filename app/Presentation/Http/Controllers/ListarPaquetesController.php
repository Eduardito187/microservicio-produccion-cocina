<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarPaquetes;
use App\Application\Produccion\Handler\ListarPaquetesHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPaquetesController
 */
class ListarPaquetesController
{
    /**
     * @var ListarPaquetesHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarPaquetesHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPaquetes);

        return response()->json($rows);
    }
}
