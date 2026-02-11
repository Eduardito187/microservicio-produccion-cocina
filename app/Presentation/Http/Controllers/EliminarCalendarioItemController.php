<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarCalendarioItemHandler;
use App\Application\Produccion\Command\EliminarCalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarCalendarioItemController
 * @package App\Presentation\Http\Controllers
 */
class EliminarCalendarioItemController
{
    /**
     * @var EliminarCalendarioItemHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarCalendarioItemHandler $handler
     */
    public function __construct(EliminarCalendarioItemHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarCalendarioItem($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
