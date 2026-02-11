<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearCalendarioHandler;
use App\Application\Produccion\Command\CrearCalendario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

/**
 * @class CrearCalendarioController
 * @package App\Presentation\Http\Controllers
 */
class CrearCalendarioController
{
    /**
     * @var CrearCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearCalendarioHandler $handler
     */
    public function __construct(CrearCalendarioHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'sucursalId' => ['required', 'string', 'max:100'],
        ]);

        $calendarioId = $this->handler->__invoke(new CrearCalendario(
            new DateTimeImmutable($data['fecha']),
            $data['sucursalId']
        ));

        return response()->json(['calendarioId' => $calendarioId], 201);
    }
}
