<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarCalendario;
use App\Application\Produccion\Handler\EliminarCalendarioHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarCalendarioController
 */
class EliminarCalendarioController
{
    /**
     * @var EliminarCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarCalendarioHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarCalendario($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
