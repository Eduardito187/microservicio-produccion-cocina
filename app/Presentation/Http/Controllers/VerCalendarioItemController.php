<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerCalendarioItemHandler;
use App\Application\Produccion\Command\VerCalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @class VerCalendarioItemController
 * @package App\Presentation\Http\Controllers
 */
class VerCalendarioItemController
{
    /**
     * @var VerCalendarioItemHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerCalendarioItemHandler $handler
     */
    public function __construct(VerCalendarioItemHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerCalendarioItem($id));
            return response()->json($row);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
