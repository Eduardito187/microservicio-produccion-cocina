<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarCalendarioItem;
use App\Application\Produccion\Handler\EliminarCalendarioItemHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarCalendarioItemController
 */
class EliminarCalendarioItemController
{
    /**
     * @var EliminarCalendarioItemHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarCalendarioItemHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarCalendarioItem($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
