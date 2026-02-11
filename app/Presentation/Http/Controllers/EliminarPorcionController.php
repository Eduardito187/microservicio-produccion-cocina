<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarPorcionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\EliminarPorcion;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPorcionController
 * @package App\Presentation\Http\Controllers
 */
class EliminarPorcionController
{
    /**
     * @var EliminarPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarPorcionHandler $handler
     */
    public function __construct(EliminarPorcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPorcion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
