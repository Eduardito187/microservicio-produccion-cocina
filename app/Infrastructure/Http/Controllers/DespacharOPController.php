<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Application\Produccion\Command\DespachadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DespacharOPController
{
    /**
     * @var DespachadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     * 
     * @param DespachadorOPHandler $handler
     */
    public function __construct(DespachadorOPHandler $handler) {
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
            new DespachadorOP(
                $data['ordenProduccionId'] ?? null
            )
        );

        return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
    }
}