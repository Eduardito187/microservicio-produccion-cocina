<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarDireccionHandler;
use App\Application\Produccion\Command\EliminarDireccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarDireccionController
 * @package App\Presentation\Http\Controllers
 */
class EliminarDireccionController
{
    /**
     * @var EliminarDireccionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarDireccionHandler $handler
     */
    public function __construct(EliminarDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarDireccion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
