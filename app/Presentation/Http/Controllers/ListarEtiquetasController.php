<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarEtiquetasHandler;
use App\Application\Produccion\Command\ListarEtiquetas;
use Illuminate\Http\JsonResponse;

class ListarEtiquetasController
{
    /**
     * @var ListarEtiquetasHandler
     */
    private ListarEtiquetasHandler $handler;

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



