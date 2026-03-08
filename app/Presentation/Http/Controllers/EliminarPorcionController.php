<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarPorcion;
use App\Application\Produccion\Handler\EliminarPorcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarPorcionController
 */
class EliminarPorcionController
{
    /**
     * @var EliminarPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarPorcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPorcion($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
