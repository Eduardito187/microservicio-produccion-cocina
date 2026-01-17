<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarEtiquetaHandler;
use App\Application\Produccion\Command\EliminarEtiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarEtiquetaController
{
    /**
     * @var EliminarEtiquetaHandler
     */
    private EliminarEtiquetaHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarEtiquetaHandler $handler
     */
    public function __construct(EliminarEtiquetaHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarEtiqueta($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



