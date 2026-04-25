<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ObtenerAgenda;
use App\Application\Produccion\Handler\ObtenerAgendaHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ObtenerAgendaController
 */
class ObtenerAgendaController
{
    /**
     * @var ObtenerAgendaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ObtenerAgendaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha_inicio' => ['sometimes', 'nullable', 'date'],
            'fecha_fin' => ['sometimes', 'nullable', 'date'],
        ]);

        $agenda = $this->handler->__invoke(new ObtenerAgenda(
            $data['fecha_inicio'] ?? null,
            $data['fecha_fin'] ?? null
        ));

        return response()->json($agenda);
    }
}
