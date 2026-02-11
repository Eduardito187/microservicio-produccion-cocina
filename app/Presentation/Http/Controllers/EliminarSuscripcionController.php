<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarSuscripcionHandler;
use App\Application\Produccion\Command\EliminarSuscripcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarSuscripcionController
 * @package App\Presentation\Http\Controllers
 */
class EliminarSuscripcionController
{
    /**
     * @var EliminarSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarSuscripcionHandler $handler
     */
    public function __construct(EliminarSuscripcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarSuscripcion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
