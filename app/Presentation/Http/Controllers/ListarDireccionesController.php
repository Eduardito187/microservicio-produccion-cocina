<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ListarDireccionesHandler;
use App\Application\Produccion\Command\ListarDirecciones;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarDireccionesController
 * @package App\Presentation\Http\Controllers
 */
class ListarDireccionesController
{
    /**
     * @var ListarDireccionesHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ListarDireccionesHandler $handler
     */
    public function __construct(ListarDireccionesHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $rows = $this->handler->__invoke(new ListarDirecciones());

        return response()->json($rows);
    }
}
