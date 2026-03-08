<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\GenerarOP;
use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Presentation\Http\Requests\GenerarOPRequest;
use DateTimeImmutable;
use DomainException;
use Illuminate\Http\JsonResponse;

/**
 * @class GenerarOPController
 */
class GenerarOPController
{
    /**
     * @var GenerarOPHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(GenerarOPHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param  Request  $request
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
                    $items
                )
            );

            return response()->json(['ordenProduccionId' => $ordenProduccionId], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
