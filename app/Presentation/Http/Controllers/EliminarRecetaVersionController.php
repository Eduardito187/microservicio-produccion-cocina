<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\EliminarRecetaVersion;
use App\Application\Produccion\Handler\EliminarRecetaVersionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarRecetaVersionController
 */
class EliminarRecetaVersionController
{
    /**
     * @var EliminarRecetaVersionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EliminarRecetaVersionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarRecetaVersion($id));

            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
