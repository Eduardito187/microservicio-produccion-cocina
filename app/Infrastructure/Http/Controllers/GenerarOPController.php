<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Application\Produccion\Command\GenerarOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

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

        $ordenProduccion = $this->handler->__invoke(
            new GenerarOP(
                $data['id'] ?? null,
                new DateTimeImmutable($data['fecha']),
                $data['sucursalId'],
                $data['items'] ?? []
            )
        );

        return response()->json(['ordenProduccion' => $ordenProduccion], 201);
    }
}
