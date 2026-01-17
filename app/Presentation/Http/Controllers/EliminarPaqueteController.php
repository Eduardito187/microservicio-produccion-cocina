<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarPaqueteHandler;
use App\Application\Produccion\Command\EliminarPaquete;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarPaqueteController
{
    /**
     * @var EliminarPaqueteHandler
     */
    private EliminarPaqueteHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarPaqueteHandler $handler
     */
    public function __construct(EliminarPaqueteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPaquete($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



