<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarRecetasHandler;
use App\Application\Produccion\Command\ListarRecetas;
use Illuminate\Http\JsonResponse;

class ListarRecetasController
{
    private $handler;

    public function __construct(ListarRecetasHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarRecetas());
        return response()->json($rows);
    }
}
