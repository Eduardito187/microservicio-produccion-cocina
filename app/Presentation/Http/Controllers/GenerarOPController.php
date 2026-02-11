<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\GenerarOPHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Presentation\Http\Requests\GenerarOPRequest;
use App\Application\Produccion\Command\GenerarOP;
use Illuminate\Http\JsonResponse;
use DateTimeImmutable;
use DomainException;

/**
 * @class GenerarOPController
 * @package App\Presentation\Http\Controllers
 */
class GenerarOPController
{
    /**
     * @var GenerarOPHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param GenerarOPHandler $handler
     */
    public function __construct(GenerarOPHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(GenerarOPRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = array_map(function (array $item): array {
            return [
                'sku' => (string) $item['sku'],
                'qty' => (int) $item['qty'],
            ];
        }, $data['items']);

        try {
            $ordenProduccionId = $this->handler->__invoke(
                new GenerarOP(
                    $data['id'] ?? null,
                    new DateTimeImmutable($data['fecha']),
                    $data['sucursalId'],
                    $items
                )
            );

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
