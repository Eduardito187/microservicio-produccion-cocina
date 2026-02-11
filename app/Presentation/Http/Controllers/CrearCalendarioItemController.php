<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearCalendarioItemHandler;
use App\Application\Produccion\Command\CrearCalendarioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearCalendarioItemController
 * @package App\Presentation\Http\Controllers
 */
class CrearCalendarioItemController
{
    /**
     * @var CrearCalendarioItemHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearCalendarioItemHandler $handler
     */
    public function __construct(CrearCalendarioItemHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'calendarioId' => ['required', 'uuid', 'exists:calendario,id'],
            'itemDespachoId' => ['required', 'uuid', 'exists:item_despacho,id'],
        ]);

        $calendarioItemId = $this->handler->__invoke(new CrearCalendarioItem(
            $data['calendarioId'],
            $data['itemDespachoId']
        ));

        return response()->json(['calendarioItemId' => $calendarioItemId], 201);
    }
}
