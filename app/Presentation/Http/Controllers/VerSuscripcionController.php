<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerSuscripcion;
use App\Application\Produccion\Handler\VerSuscripcionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerSuscripcionController
 */
class VerSuscripcionController
{
    /**
     * @var VerSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerSuscripcionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerSuscripcion($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
