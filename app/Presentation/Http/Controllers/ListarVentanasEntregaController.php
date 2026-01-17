<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarVentanasEntregaHandler;
use App\Application\Produccion\Command\ListarVentanasEntrega;
use Illuminate\Http\JsonResponse;

class ListarVentanasEntregaController
{
    /**
     * @var ListarVentanasEntregaHandler
     */
    private ListarVentanasEntregaHandler $handler;

    /**
     * Constructor
     *
     * @param ListarVentanasEntregaHandler $handler
     */
    public function __construct(ListarVentanasEntregaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarVentanasEntrega());

        return response()->json($rows);
    }
}



