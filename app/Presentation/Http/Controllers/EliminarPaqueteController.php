<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarPaqueteHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\EliminarPaquete;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPaqueteController
 * @package App\Presentation\Http\Controllers
 */
class EliminarPaqueteController
{
    /**
     * @var EliminarPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarPaqueteHandler $handler
     */
    public function __construct(EliminarPaqueteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPaquete($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
