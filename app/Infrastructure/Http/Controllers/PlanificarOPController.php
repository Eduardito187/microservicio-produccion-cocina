<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Produccion\Handler\PlanificadorOPHandler;
use App\Application\Produccion\Command\PlanificarOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $ordenProduccionId = $this->handler->__invoke(
            new PlanificarOP(
                $data['ordenProduccionId'] ?? null
            )
        );

        return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
    }
}