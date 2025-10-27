<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\DespachadorOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\DespachadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

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

        try {
            $ordenProduccionId = $this->handler->__invoke(
                new DespachadorOP(
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