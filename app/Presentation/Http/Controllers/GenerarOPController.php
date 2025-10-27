<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\GenerarOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\GenerarOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;
use DomainException;

class GenerarOPController
{
    /**
     * @var GenerarOPHandler
     */
    private $handler;

    /**
     * Constructor
     * 
     * @param GenerarOPHandler $handler
     */
    public function __construct(GenerarOPHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required','date'],
            'sucursalId' => ['required','string'],
            'items' => ['array']
        ]);

        try {
            $ordenProduccionId = $this->handler->__invoke(
                new GenerarOP(
                    $data['id'] ?? null,
                    new DateTimeImmutable($data['fecha']),
                    $data['sucursalId'],
                    $data['items'] ?? []
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
