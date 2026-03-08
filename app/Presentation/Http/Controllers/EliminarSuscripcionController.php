<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarSuscripcion;
use App\Application\Produccion\Handler\EliminarSuscripcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarSuscripcionController
 */
class EliminarSuscripcionController
{
    /**
     * @var EliminarSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarSuscripcion($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
