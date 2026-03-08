<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarCalendarios;
use App\Application\Produccion\Handler\ListarCalendariosHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendariosController
 */
class ListarCalendariosController
{
    /**
     * @var ListarCalendariosHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarCalendariosHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarCalendarios);

        return response()->json($rows);
    }
}
