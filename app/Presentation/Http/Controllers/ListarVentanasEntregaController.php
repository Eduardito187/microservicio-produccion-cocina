<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarVentanasEntregaHandler;
use App\Application\Produccion\Command\ListarVentanasEntrega;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarVentanasEntregaController
 * @package App\Presentation\Http\Controllers
 */
class ListarVentanasEntregaController
{
    /**
     * @var ListarVentanasEntregaHandler
     */
    private $handler;

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
