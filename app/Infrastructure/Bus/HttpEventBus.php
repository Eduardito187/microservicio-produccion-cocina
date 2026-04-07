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
        Http::retry(3, 500, throw: false)->connectTimeout(3)->timeout($this->eventBusTimeout())->acceptJson()
            ->asJson()->withHeaders(['X-EventBus-Token' => $this->eventBusSecret()])
            ->post(
                $this->eventBusEndpoint(),
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

    private function eventBusEndpoint(): string
    {
        $value = getenv('EVENTBUS_ENDPOINT');
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return (string) config('app.endpoint', 'http://127.0.0.1:8000/api/event-bus');
    }

    private function eventBusSecret(): string
    {
        $value = getenv('EVENTBUS_SECRET');
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    private function eventBusTimeout(): int
    {
        $value = getenv('EVENTBUS_TIMEOUT');
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return (int) config('app.timeout', 5);
    }
}
