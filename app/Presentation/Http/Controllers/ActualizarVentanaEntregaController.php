<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarVentanaEntrega;
use App\Application\Produccion\Handler\ActualizarVentanaEntregaHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarVentanaEntregaController
 */
class ActualizarVentanaEntregaController
{
    /**
     * @var ActualizarVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarVentanaEntregaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after:desde'],
        ]);

        try {
            $ventanaId = $this->handler->__invoke(new ActualizarVentanaEntrega(
                $id,
                new DateTimeImmutable($data['desde']),
                new DateTimeImmutable($data['hasta'])
            ));

            return response()->json(['ventanaEntregaId' => $ventanaId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
