<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerDireccionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerDireccion;
use Illuminate\Http\JsonResponse;

/**
 * @class VerDireccionController
 * @package App\Presentation\Http\Controllers
 */
class VerDireccionController
{
    /**
     * @var VerDireccionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerDireccionHandler $handler
     */
    public function __construct(VerDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerDireccion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
