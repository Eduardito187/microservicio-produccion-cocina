<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarEtiquetaHandler;
use App\Application\Produccion\Command\EliminarEtiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarEtiquetaController
 * @package App\Presentation\Http\Controllers
 */
class EliminarEtiquetaController
{
    /**
     * @var EliminarEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarEtiquetaHandler $handler
     */
    public function __construct(EliminarEtiquetaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarEtiqueta($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
