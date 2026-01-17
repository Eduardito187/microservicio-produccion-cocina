<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearCalendarioItemHandler;
use App\Application\Produccion\Command\CrearCalendarioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrearCalendarioItemController
{
    /**
     * @var CrearCalendarioItemHandler
     */
    private CrearCalendarioItemHandler $handler;

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
            'calendarioId' => ['required', 'int', 'exists:calendario,id'],
            'itemDespachoId' => ['required', 'int', 'exists:item_despacho,id'],
        ]);

        $calendarioItemId = $this->handler->__invoke(new CrearCalendarioItem(
            $data['calendarioId'],
            $data['itemDespachoId']
        ));

        return response()->json(['calendarioItemId' => $calendarioItemId], 201);
    }
}



