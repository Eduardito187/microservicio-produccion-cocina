<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerProducto;
use App\Application\Produccion\Handler\VerProductoHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerProductoController
 */
class VerProductoController
{
    /**
     * @var VerProductoHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerProductoHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerProducto($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
