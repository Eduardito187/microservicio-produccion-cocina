<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerVentanaEntrega;
use App\Application\Produccion\Handler\VerVentanaEntregaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerVentanaEntregaController
 */
class VerVentanaEntregaController
{
    /**
     * @var VerVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerVentanaEntrega($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
