<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarSuscripcionesHandler;
use App\Application\Produccion\Command\ListarSuscripciones;
use Illuminate\Http\JsonResponse;

class ListarSuscripcionesController
{
    /**
     * @var ListarSuscripcionesHandler
     */
    private ListarSuscripcionesHandler $handler;

    /**
     * Constructor
     *
     * @param ListarSuscripcionesHandler $handler
     */
    public function __construct(ListarSuscripcionesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarSuscripciones());

        return response()->json($rows);
    }
}



