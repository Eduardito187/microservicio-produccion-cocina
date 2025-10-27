<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\PlanificadorOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\PlanificarOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

class PlanificarOPController
{
    /**
     * @var PlanificadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     * 
     * @param PlanificadorOPHandler $handler
     */
    public function __construct(PlanificadorOPHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['ordenProduccionId' => ['required','int']]);

        try {
            $ordenProduccionId = $this->handler->__invoke(
                new PlanificarOP(
                    $data['ordenProduccionId'] ?? null
                )
            );

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}