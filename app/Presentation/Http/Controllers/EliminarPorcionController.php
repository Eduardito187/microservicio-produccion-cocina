<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarPorcionHandler;
use App\Application\Produccion\Command\EliminarPorcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarPorcionController
{
    /**
     * @var EliminarPorcionHandler
     */
    private EliminarPorcionHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarPorcionHandler $handler
     */
    public function __construct(EliminarPorcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarPorcion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



