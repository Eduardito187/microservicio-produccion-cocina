<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Produccion\Handler\ProcesadorOPHandler;
use App\Application\Produccion\Command\ProcesadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcesarOPController
{
    /**
     * @var ProcesadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     * 
     * @param ProcesadorOPHandler $handler
     */
    public function __construct(ProcesadorOPHandler $handler) {
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
            new ProcesadorOP(
                $data['ordenProduccionId'] ?? null
            )
        );

        return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
    }
}