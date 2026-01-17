<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerRecetaVersionHandler;
use App\Application\Produccion\Command\VerRecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class VerRecetaVersionController
{
    /**
     * @var VerRecetaVersionHandler
     */
    private VerRecetaVersionHandler $handler;

    /**
     * Constructor
     *
     * @param VerRecetaVersionHandler $handler
     */
    public function __construct(VerRecetaVersionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerRecetaVersion($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}



