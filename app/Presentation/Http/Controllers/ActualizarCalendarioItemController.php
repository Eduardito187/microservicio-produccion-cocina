<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarCalendarioItemHandler;
use App\Application\Produccion\Command\ActualizarCalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizarCalendarioItemController
{
    /**
     * @var ActualizarCalendarioItemHandler
     */
    private ActualizarCalendarioItemHandler $handler;

    /**
     * Constructor
     *
     * @param ActualizarCalendarioItemHandler $handler
     */
    public function __construct(ActualizarCalendarioItemHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'calendarioId' => ['required', 'int', 'exists:calendario,id'],
            'itemDespachoId' => ['required', 'int', 'exists:item_despacho,id'],
        ]);

        try {
            $calendarioItemId = $this->handler->__invoke(new ActualizarCalendarioItem(
                $id,
                $data['calendarioId'],
                $data['itemDespachoId']
            ));

            return response()->json(['calendarioItemId' => $calendarioItemId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
