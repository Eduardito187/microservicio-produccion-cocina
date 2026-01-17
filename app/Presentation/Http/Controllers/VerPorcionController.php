<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerPorcionHandler;
use App\Application\Produccion\Command\VerPorcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class VerPorcionController
{
    /**
     * @var VerPorcionHandler
     */
    private VerPorcionHandler $handler;

    /**
     * Constructor
     *
     * @param VerPorcionHandler $handler
     */
    public function __construct(VerPorcionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerPorcion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



