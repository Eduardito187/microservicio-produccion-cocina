<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\PlanificadorOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\PlanificarOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

/**
 * @class PlanificarOPController
 * @package App\Presentation\Http\Controllers
 */
class PlanificarOPController
{
    /**
     * @var PlanificadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param PlanificadorOPHandler $handler
     */
    public function __construct(PlanificadorOPHandler $handler) {
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
                'ordenProduccionId' => ['required', 'uuid', 'exists:orden_produccion,id'],
                'estacionId' => ['required', 'uuid', 'exists:estacion,id'],
                'recetaVersionId' => ['required', 'uuid', 'exists:receta_version,id'],
                'porcionId' => ['required', 'uuid', 'exists:porcion,id']
            ]
        );

        try {
            $ordenProduccionId = $this->handler->__invoke(new PlanificarOP($data));

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
