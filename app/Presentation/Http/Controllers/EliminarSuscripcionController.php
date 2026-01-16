<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarSuscripcionHandler;
use App\Application\Produccion\Command\EliminarSuscripcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarSuscripcionController
{
    /**
     * @var EliminarSuscripcionHandler
     */
    private EliminarSuscripcionHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarSuscripcionHandler $handler
     */
    public function __construct(EliminarSuscripcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarSuscripcion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
