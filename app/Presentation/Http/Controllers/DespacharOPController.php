<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\DespachadorOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\DespachadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

/**
 * @class DespacharOPController
 * @package App\Presentation\Http\Controllers
 */
class DespacharOPController
{
    /**
     * @var DespachadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param DespachadorOPHandler $handler
     */
    public function __construct(DespachadorOPHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'ordenProduccionId' => ['required', 'uuid'],
                'itemsDespacho' => ['required', 'array'],
                'itemsDespacho.*.sku' => ['required', 'string'],
                'itemsDespacho.*.recetaVersionId' => ['required', 'uuid', 'exists:receta_version,id'],
                'pacienteId' => ['required', 'uuid'],
                'direccionId' => ['required', 'uuid'],
                'ventanaEntrega' => ['required', 'uuid']
            ]
        );

        try {
            $ordenProduccionId = $this->handler->__invoke(new DespachadorOP($data));

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

    }
}
