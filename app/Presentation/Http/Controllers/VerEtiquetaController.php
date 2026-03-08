<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerEtiqueta;
use App\Application\Produccion\Handler\VerEtiquetaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerEtiquetaController
 */
class VerEtiquetaController
{
    /**
     * @var VerEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerEtiquetaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerEtiqueta($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
