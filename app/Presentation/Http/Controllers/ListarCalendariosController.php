<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarCalendariosHandler;
use App\Application\Produccion\Command\ListarCalendarios;
use Illuminate\Http\JsonResponse;

class ListarCalendariosController
{
    /**
     * @var ListarCalendariosHandler
     */
    private ListarCalendariosHandler $handler;

    /**
     * Constructor
     *
     * @param ListarCalendariosHandler $handler
     */
    public function __construct(ListarCalendariosHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarCalendarios());

        return response()->json($rows);
    }
}



