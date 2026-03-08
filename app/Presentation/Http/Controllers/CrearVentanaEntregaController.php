<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearVentanaEntrega;
use App\Application\Produccion\Handler\CrearVentanaEntregaHandler;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearVentanaEntregaController
 */
class CrearVentanaEntregaController
{
    /**
     * @var CrearVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after:desde'],
        ]);

        $ventanaId = $this->handler->__invoke(new CrearVentanaEntrega(
            new DateTimeImmutable($data['desde']),
            new DateTimeImmutable($data['hasta'])
        ));

        return response()->json(['ventanaEntregaId' => $ventanaId], 201);
    }
}
