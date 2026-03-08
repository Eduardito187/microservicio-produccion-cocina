<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarEtiqueta;
use App\Application\Produccion\Handler\EliminarEtiquetaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarEtiquetaController
 */
class EliminarEtiquetaController
{
    /**
     * @var EliminarEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarEtiquetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarEtiqueta($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
