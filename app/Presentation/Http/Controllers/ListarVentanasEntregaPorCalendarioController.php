<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ListarVentanasEntregaPorCalendario;
use App\Application\Produccion\Handler\ListarVentanasEntregaPorCalendarioHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class ListarVentanasEntregaPorCalendarioController
 */
class ListarVentanasEntregaPorCalendarioController
{
    /**
     * @var ListarVentanasEntregaPorCalendarioHandler
     */
    private $handler;

    public function __construct(ListarVentanasEntregaPorCalendarioHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $rows = $this->handler->__invoke(new ListarVentanasEntregaPorCalendario($id));

            return response()->json($rows);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
