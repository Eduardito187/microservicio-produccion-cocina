<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarDirecciones;
use App\Application\Produccion\Handler\ListarDireccionesHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarDireccionesController
 */
class ListarDireccionesController
{
    /**
     * @var ListarDireccionesHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarDireccionesHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarDirecciones);

        return response()->json($rows);
    }
}
