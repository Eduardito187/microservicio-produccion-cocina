<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarCalendarioHandler;
use App\Application\Produccion\Command\EliminarCalendario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarCalendarioController
 * @package App\Presentation\Http\Controllers
 */
class EliminarCalendarioController
{
    /**
     * @var EliminarCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarCalendarioHandler $handler
     */
    public function __construct(EliminarCalendarioHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarCalendario($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
