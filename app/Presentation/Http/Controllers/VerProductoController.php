<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerProductoHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerProducto;
use Illuminate\Http\JsonResponse;

/**
 * @class VerProductoController
 * @package App\Presentation\Http\Controllers
 */
class VerProductoController
{
    /**
     * @var VerProductoHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerProductoHandler $handler
     */
    public function __construct(VerProductoHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerProducto($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
