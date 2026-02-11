<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarCalendarioItemsHandler;
use App\Application\Produccion\Command\ListarCalendarioItems;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendarioItemsController
 * @package App\Presentation\Http\Controllers
 */
class ListarCalendarioItemsController
{
    /**
     * @var ListarCalendarioItemsHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarCalendarioItemsHandler $handler
     */
    public function __construct(ListarCalendarioItemsHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarCalendarioItems());

        return response()->json($rows);
    }
}
