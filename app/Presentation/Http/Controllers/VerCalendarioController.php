<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerCalendarioHandler;
use App\Application\Produccion\Command\VerCalendario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class VerCalendarioController
{
    /**
     * @var VerCalendarioHandler
     */
    private VerCalendarioHandler $handler;

    /**
     * Constructor
     *
     * @param VerCalendarioHandler $handler
     */
    public function __construct(VerCalendarioHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerCalendario($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



