<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarCalendarioItemHandler;
use App\Application\Produccion\Command\EliminarCalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarCalendarioItemController
{
    /**
     * @var EliminarCalendarioItemHandler
     */
    private EliminarCalendarioItemHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarCalendarioItemHandler $handler
     */
    public function __construct(EliminarCalendarioItemHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarCalendarioItem($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



