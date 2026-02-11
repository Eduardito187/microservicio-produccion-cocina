<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarCalendariosHandler;
use App\Application\Produccion\Command\ListarCalendarios;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendariosController
 * @package App\Presentation\Http\Controllers
 */
class ListarCalendariosController
{
    /**
     * @var ListarCalendariosHandler
     */
    private $handler;

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
