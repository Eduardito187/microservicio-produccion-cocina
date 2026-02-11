<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerEtiquetaHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerEtiqueta;
use Illuminate\Http\JsonResponse;

/**
 * @class VerEtiquetaController
 * @package App\Presentation\Http\Controllers
 */
class VerEtiquetaController
{
    /**
     * @var VerEtiquetaHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerEtiquetaHandler $handler
     */
    public function __construct(VerEtiquetaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerEtiqueta($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
