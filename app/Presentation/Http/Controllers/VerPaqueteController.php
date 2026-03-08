<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerPaquete;
use App\Application\Produccion\Handler\VerPaqueteHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerPaqueteController
 */
class VerPaqueteController
{
    /**
     * @var VerPaqueteHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerPaqueteHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPaquete($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
