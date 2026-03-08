<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\VerCalendarioItem;
use App\Application\Produccion\Handler\VerCalendarioItemHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerCalendarioItemController
 */
class VerCalendarioItemController
{
    /**
     * @var VerCalendarioItemHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(VerCalendarioItemHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerCalendarioItem($id));

            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
