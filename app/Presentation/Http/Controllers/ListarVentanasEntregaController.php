<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarVentanasEntrega;
use App\Application\Produccion\Handler\ListarVentanasEntregaHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarVentanasEntregaController
 */
class ListarVentanasEntregaController
{
    /**
     * @var ListarVentanasEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarVentanasEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarVentanasEntrega);

        return response()->json($rows);
    }
}
