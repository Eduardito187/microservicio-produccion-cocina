<?php

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Model\InboundEvent;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventBusController
{
    /**
     * Summary of __invoke
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $token = $request->header('X-EventBus-Token');
        if ($token !== env('EVENTBUS_SECRET')) {
            return response()->json(
                ['message' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = $request->validate([
            'event' => ['required','string','max:150'],
            'occurred_on' => ['nullable','string'],
            'payload' => ['required','array'],
            'event_id' => ['nullable','string','max:100'],
        ]);

        $eventId = $data['event_id'] ?? $this->hashEnvelope($data);
        $already = InboundEvent::query()->where('event_id', $eventId)->exists();

        if ($already) {
            return response()->json(['status' => 'duplicate'], Response::HTTP_OK);
        }

        InboundEvent::create([
            'event_id' => $eventId,
            'event_name' => $data['event'],
            'occurred_on' => $data['occurred_on'] ?? null,
            'payload' => json_encode($data['payload']),
        ]);

        switch ($data['event']) {
            case 'App\Domain\Produccion\Events\ItemDespachoCreado':
                break;
            case 'App\Domain\Produccion\Events\ListaDespachoCreada':
                break;
            case 'App\Domain\Produccion\Events\OrdenItemCreada':
                break;
            case 'App\Domain\Produccion\Events\OrdenProduccionCerrada':
                break;
            case 'App\Domain\Produccion\Events\OrdenProduccionCreada':
                break;
            case 'App\Domain\Produccion\Events\OrdenProduccionPlanificada':
                break;
            case 'App\Domain\Produccion\Events\OrdenProduccionProcesada':
                break;
            case 'App\Domain\Produccion\Events\ProduccionBatchCreado':
                break;
            default:
                break;
        }

        return response()->json(
            [
                'status' => 'ok'
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @param array $data
     * @return string
     */
    private function hashEnvelope(array $data): string
    {
        return hash(
            'sha256',
            json_encode(
                [
                    $data['event'] ?? '',
                    $data['occurred_on'] ?? '',
                    $data['payload'] ?? []
                ],
                 JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
            )
        );
    }
}