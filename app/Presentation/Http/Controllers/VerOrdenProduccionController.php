<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerOrdenProduccion;
use App\Application\Produccion\Handler\VerOrdenProduccionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class VerOrdenProduccionController
 */
class VerOrdenProduccionController
{
    /**
     * @var VerOrdenProduccionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerOrdenProduccionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json($this->handler->__invoke(new VerOrdenProduccion($id)));
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
