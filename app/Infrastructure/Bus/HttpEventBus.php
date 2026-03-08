<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Bus;

use App\Application\Shared\BusInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

/**
 * @class HttpEventBus
 */
class HttpEventBus implements BusInterface
{
    public function publish(string $eventId, string $name, array $payload, DateTimeImmutable $occurredOn, array $meta = []): void
    {
        Http::retry(3, 500, throw: false)->connectTimeout(3)->timeout(env('EVENTBUS_TIMEOUT'))->acceptJson()
            ->asJson()->withHeaders(['X-EventBus-Token' => env('EVENTBUS_SECRET')])
            ->post(
                env('EVENTBUS_ENDPOINT'),
                [
                    'event' => $name,
                    'occurred_on' => $occurredOn->format(DATE_ATOM),
                    'event_id' => $eventId,
                    'schema_version' => $meta['schema_version'] ?? null,
                    'correlation_id' => $meta['correlation_id'] ?? null,
                    'aggregate_id' => $meta['aggregate_id'] ?? null,
                    'payload' => $payload,
                ]
            )->throw();
    }
}
