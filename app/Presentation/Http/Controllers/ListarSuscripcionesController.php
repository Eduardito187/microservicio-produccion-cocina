<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarSuscripciones;
use App\Application\Produccion\Handler\ListarSuscripcionesHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarSuscripcionesController
 */
class ListarSuscripcionesController
{
    /**
     * @var ListarSuscripcionesHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarSuscripcionesHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarSuscripciones);

        return response()->json($rows);
    }
}
