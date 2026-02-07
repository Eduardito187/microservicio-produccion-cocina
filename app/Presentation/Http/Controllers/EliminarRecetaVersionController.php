<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\EliminarRecetaVersionHandler;
use App\Application\Produccion\Command\EliminarRecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EliminarRecetaVersionController
{
    /**
     * @var EliminarRecetaVersionHandler
     */
    private EliminarRecetaVersionHandler $handler;

    /**
     * Constructor
     *
     * @param EliminarRecetaVersionHandler $handler
     */
    public function __construct(EliminarRecetaVersionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->handler->__invoke(new EliminarRecetaVersion($id));
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



