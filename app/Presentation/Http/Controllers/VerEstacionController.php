<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerEstacionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerEstacion;
use Illuminate\Http\JsonResponse;

/**
 * @class VerEstacionController
 * @package App\Presentation\Http\Controllers
 */
class VerEstacionController
{
    /**
     * @var VerEstacionHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerEstacionHandler $handler
     */
    public function __construct(VerEstacionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerEstacion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
