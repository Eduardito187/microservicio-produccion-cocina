<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarEstacionHandler;
use App\Application\Produccion\Command\EliminarEstacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarEstacionController
{
    /**
     * @var EliminarEstacionHandler
     */
    private EliminarEstacionHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarEstacionHandler $handler
     */
    public function __construct(EliminarEstacionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarEstacion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



