<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ProcesadorOPHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Application\Produccion\Command\ProcesadorOP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

/**
 * @class ProcesarOPController
 * @package App\Presentation\Http\Controllers
 */
class ProcesarOPController
{
    /**
     * @var ProcesadorOPHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ProcesadorOPHandler $handler
     */
    public function __construct(ProcesadorOPHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['ordenProduccionId' => ['required', 'uuid']]);

        try {
            $ordenProduccionId = $this->handler->__invoke(new ProcesadorOP($data['ordenProduccionId']));

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
