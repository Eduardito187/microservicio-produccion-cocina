<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerRecetaVersion;
use App\Application\Produccion\Handler\VerRecetaVersionHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerRecetaVersionController
 */
class VerRecetaVersionController
{
    /**
     * @var VerRecetaVersionHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerRecetaVersionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerRecetaVersion($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
