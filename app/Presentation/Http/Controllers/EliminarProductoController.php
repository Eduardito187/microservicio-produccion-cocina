<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarProductoHandler;
use App\Application\Produccion\Command\EliminarProducto;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class EliminarProductoController
 * @package App\Presentation\Http\Controllers
 */
class EliminarProductoController
{
    /**
     * @var EliminarProductoHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param EliminarProductoHandler $handler
     */
    public function __construct(EliminarProductoHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarProducto($id));
            return response()->json(null, 204);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
