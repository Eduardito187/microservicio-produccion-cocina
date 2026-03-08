<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarPaquete;
use App\Application\Produccion\Handler\EliminarPaqueteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPaqueteController
 */
class EliminarPaqueteController
{
    /**
     * @var EliminarPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarPaqueteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPaquete($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
