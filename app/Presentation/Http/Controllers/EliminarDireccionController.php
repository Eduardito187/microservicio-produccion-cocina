<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarDireccionHandler;
use App\Application\Produccion\Command\EliminarDireccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarDireccionController
{
    /**
     * @var EliminarDireccionHandler
     */
    private EliminarDireccionHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarDireccionHandler $handler
     */
    public function __construct(EliminarDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarDireccion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



