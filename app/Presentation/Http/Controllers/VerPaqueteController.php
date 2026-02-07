<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerPaqueteHandler;
use App\Application\Produccion\Command\VerPaquete;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class VerPaqueteController
{
    /**
     * @var VerPaqueteHandler
     */
    private VerPaqueteHandler $handler;

    /**
     * Constructor
     *
     * @param VerPaqueteHandler $handler
     */
    public function __construct(VerPaqueteHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPaquete($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



