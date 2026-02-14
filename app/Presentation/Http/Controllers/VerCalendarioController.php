<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\VerCalendarioHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Application\Produccion\Command\VerCalendario;
use Illuminate\Http\JsonResponse;

/**
 * @class VerCalendarioController
 * @package App\Presentation\Http\Controllers
 */
class VerCalendarioController
{
    /**
     * @var VerCalendarioHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param VerCalendarioHandler $handler
     */
    public function __construct(VerCalendarioHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(string $id): JsonResponse
    {
        try {
            $row = $this->handler->__invoke(new VerCalendario($id));
            return response()->json($row);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
