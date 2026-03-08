<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarEtiquetas;
use App\Application\Produccion\Handler\ListarEtiquetasHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarEtiquetasController
 */
class ListarEtiquetasController
{
    /**
     * @var ListarEtiquetasHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarEtiquetasHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarEtiquetas);

        return response()->json($rows);
    }
}
