<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerPaqueteHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPaquete;
use Illuminate\Http\JsonResponse;

/**
 * @class VerPaqueteController
 * @package App\Presentation\Http\Controllers
 */
class VerPaqueteController
{
    /**
     * @var VerPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerPaqueteHandler $handler
     */
    public function __construct(VerPaqueteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPaquete($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
