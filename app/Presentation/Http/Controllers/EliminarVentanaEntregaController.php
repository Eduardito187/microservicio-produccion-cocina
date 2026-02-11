<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarVentanaEntregaHandler;
use App\Application\Produccion\Command\EliminarVentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarVentanaEntregaController
 * @package App\Presentation\Http\Controllers
 */
class EliminarVentanaEntregaController
{
    /**
     * @var EliminarVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarVentanaEntregaHandler $handler
     */
    public function __construct(EliminarVentanaEntregaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarVentanaEntrega($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
