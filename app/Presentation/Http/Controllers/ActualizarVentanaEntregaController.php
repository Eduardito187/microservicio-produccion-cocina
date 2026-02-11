<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarVentanaEntregaHandler;
use App\Application\Produccion\Command\ActualizarVentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

/**
 * @class ActualizarVentanaEntregaController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarVentanaEntregaController
{
    /**
     * @var ActualizarVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarVentanaEntregaHandler $handler
     */
    public function __construct(ActualizarVentanaEntregaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
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
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
