<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ProcesadorOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\ProcesadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

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

        try {
            $ordenProduccionId = $this->handler->__invoke(
                new ProcesadorOP(
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