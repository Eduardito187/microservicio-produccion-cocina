<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarPaquetesHandler;
use App\Application\Produccion\Command\ListarPaquetes;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPaquetesController
 * @package App\Presentation\Http\Controllers
 */
class ListarPaquetesController
{
    /**
     * @var ListarPaquetesHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarPaquetesHandler $handler
     */
    public function __construct(ListarPaquetesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPaquetes());

        return response()->json($rows);
    }
}
