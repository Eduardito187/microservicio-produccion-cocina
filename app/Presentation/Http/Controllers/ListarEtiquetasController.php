<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarEtiquetasHandler;
use App\Application\Produccion\Command\ListarEtiquetas;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarEtiquetasController
 * @package App\Presentation\Http\Controllers
 */
class ListarEtiquetasController
{
    /**
     * @var ListarEtiquetasHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarEtiquetasHandler $handler
     */
    public function __construct(ListarEtiquetasHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarEtiquetas());

        return response()->json($rows);
    }
}
