<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarCalendarioHandler;
use App\Application\Produccion\Command\ActualizarCalendario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

/**
 * @class ActualizarCalendarioController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarCalendarioController
{
    /**
     * @var ActualizarCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarCalendarioHandler $handler
     */
    public function __construct(ActualizarCalendarioHandler $handler) {
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
            'fecha' => ['required', 'date'],
            'sucursalId' => ['required', 'string', 'max:100'],
        ]);

        try {
            $calendarioId = $this->handler->__invoke(new ActualizarCalendario(
                $id,
                new DateTimeImmutable($data['fecha']),
                $data['sucursalId']
            ));

            return response()->json(['calendarioId' => $calendarioId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
