<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerSuscripcionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerSuscripcion;
use Illuminate\Http\JsonResponse;

/**
 * @class VerSuscripcionController
 * @package App\Presentation\Http\Controllers
 */
class VerSuscripcionController
{
    /**
     * @var VerSuscripcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerSuscripcionHandler $handler
     */
    public function __construct(VerSuscripcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerSuscripcion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
