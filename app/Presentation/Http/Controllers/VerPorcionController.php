<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerPorcionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPorcion;
use Illuminate\Http\JsonResponse;

/**
 * @class VerPorcionController
 * @package App\Presentation\Http\Controllers
 */
class VerPorcionController
{
    /**
     * @var VerPorcionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerPorcionHandler $handler
     */
    public function __construct(VerPorcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPorcion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
