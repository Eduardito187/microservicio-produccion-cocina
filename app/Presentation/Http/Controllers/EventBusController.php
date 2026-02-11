<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Produccion\Command\RegistrarInboundEvent;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Illuminate\Http\Request;

/**
 * @class EventBusController
 * @package App\Presentation\Http\Controllers
 */
class EventBusController
{
    /**
     * @var RegistrarInboundEventHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param RegistrarInboundEventHandler $handler
     */
    public function __construct(RegistrarInboundEventHandler $handler) {
        $this->handler = $handler;
    }

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
            'event_id' => ['nullable','uuid'],
            'schema_version' => ['required','integer'],
            'correlation_id' => ['nullable','uuid'],
            'aggregate_id' => ['nullable','string','max:100'],
        ]);

        $eventId = $data['event_id'] ?? (string) \Illuminate\Support\Str::uuid();
        try {
            $isDuplicate = $this->handler->__invoke(new RegistrarInboundEvent(
                $eventId,
                $data['event'],
                $data['occurred_on'] ?? null,
                json_encode($data['payload']),
                $data['schema_version'] ?? null,
                $data['correlation_id'] ?? null
            ));
        } catch (InvalidArgumentException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($isDuplicate) {
            return response()->json(['status' => 'duplicate'], Response::HTTP_OK);
        }

        switch ($data['event']) {
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
