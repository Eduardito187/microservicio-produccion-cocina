<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class DespacharOPController
 */
class DespacharOPController
{
    /**
     * @var DespachadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(DespachadorOPHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'ordenProduccionId' => ['required', 'uuid'],
                'itemsDespacho' => ['required', 'array', 'min:1'],
                'itemsDespacho.*.sku' => ['required', 'string'],
                'pacienteId' => ['required', 'uuid'],
                'direccionId' => ['required', 'uuid'],
                'ventanaEntrega' => ['required', 'uuid'],
            ]
        );

        try {
            $ordenProduccionId = $this->handler->__invoke(new DespachadorOP($data));

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

    }
}
