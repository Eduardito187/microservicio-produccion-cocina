<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerDireccionHandler;
use App\Application\Produccion\Command\VerDireccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class VerDireccionController
{
    /**
     * @var VerDireccionHandler
     */
    private VerDireccionHandler $handler;

    /**
     * Constructor
     *
     * @param VerDireccionHandler $handler
     */
    public function __construct(VerDireccionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerDireccion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



