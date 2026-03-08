<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\PlanificarOP;
use App\Application\Produccion\Handler\PlanificadorOPHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class PlanificarOPController
 */
class PlanificarOPController
{
    /**
     * @var PlanificadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(PlanificadorOPHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'ordenProduccionId' => ['required', 'uuid', 'exists:orden_produccion,id'],
                'porcionId' => ['required', 'uuid', 'exists:porcion,id'],
            ]
        );

        try {
            $ordenProduccionId = $this->handler->__invoke(new PlanificarOP($data));

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
