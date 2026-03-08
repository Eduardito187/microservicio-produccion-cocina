<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearCalendario;
use App\Application\Produccion\Handler\CrearCalendarioHandler;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearCalendarioController
 */
class CrearCalendarioController
{
    /**
     * @var CrearCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearCalendarioHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        $calendarioId = $this->handler->__invoke(new CrearCalendario(
            new DateTimeImmutable($data['fecha'])
        ));

        return response()->json(['calendarioId' => $calendarioId], 201);
    }
}
