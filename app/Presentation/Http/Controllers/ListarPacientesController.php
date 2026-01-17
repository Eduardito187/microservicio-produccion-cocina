<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarPacientesHandler;
use App\Application\Produccion\Command\ListarPacientes;
use Illuminate\Http\JsonResponse;

class ListarPacientesController
{
    /**
     * @var ListarPacientesHandler
     */
    private ListarPacientesHandler $handler;

    /**
     * Constructor
     *
     * @param ListarPacientesHandler $handler
     */
    public function __construct(ListarPacientesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPacientes());

        return response()->json($rows);
    }
}



