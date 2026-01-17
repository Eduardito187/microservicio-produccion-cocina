<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearRecetaVersionHandler;
use App\Application\Produccion\Command\CrearRecetaVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrearRecetaVersionController
{
    /**
     * @var CrearRecetaVersionHandler
     */
    private CrearRecetaVersionHandler $handler;

    /**
     * Constructor
     *
     * @param CrearRecetaVersionHandler $handler
     */
    public function __construct(CrearRecetaVersionHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nutrientes' => ['nullable', 'array'],
            'ingredientes' => ['nullable', 'array'],
            'version' => ['nullable', 'int', 'min:1'],
        ]);

        $recetaVersionId = $this->handler->__invoke(new CrearRecetaVersion(
            $data['nombre'],
            $data['nutrientes'] ?? null,
            $data['ingredientes'] ?? null,
            $data['version'] ?? 1
        ));

        return response()->json(['recetaVersionId' => $recetaVersionId], 201);
    }
}



