<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerVentanaEntregaHandler;
use App\Application\Produccion\Command\VerVentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerVentanaEntregaController
 * @package App\Presentation\Http\Controllers
 */
class VerVentanaEntregaController
{
    /**
     * @var VerVentanaEntregaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerVentanaEntregaHandler $handler
     */
    public function __construct(VerVentanaEntregaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerVentanaEntrega($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
