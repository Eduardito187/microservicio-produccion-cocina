<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarPacientes;
use App\Application\Produccion\Handler\ListarPacientesHandler;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarPacientesController
 */
class ListarPacientesController
{
    /**
     * @var ListarPacientesHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ListarPacientesHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarPacientes);

        return response()->json($rows);
    }
}
