<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarCalendarioItems;
use App\Application\Produccion\Handler\ListarCalendarioItemsHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarCalendarioItemsController
 */
class ListarCalendarioItemsController
{
    /**
     * @var ListarCalendarioItemsHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarCalendarioItemsHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarCalendarioItems);

        return response()->json($rows);
    }
}
