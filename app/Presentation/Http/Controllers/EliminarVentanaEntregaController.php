<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarVentanaEntrega;
use App\Application\Produccion\Handler\EliminarVentanaEntregaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarVentanaEntregaController
 */
class EliminarVentanaEntregaController
{
    /**
     * @var EliminarVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarVentanaEntrega($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
