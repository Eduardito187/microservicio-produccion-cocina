<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarCalendario;
use App\Application\Produccion\Handler\ActualizarCalendarioHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarCalendarioController
 */
class ActualizarCalendarioController
{
    /**
     * @var ActualizarCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarCalendarioHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        try {
            $calendarioId = $this->handler->__invoke(new ActualizarCalendario(
                $id,
                new DateTimeImmutable($data['fecha'])
            ));

            return response()->json(['calendarioId' => $calendarioId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
