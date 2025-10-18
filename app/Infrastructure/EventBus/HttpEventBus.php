<?php

namespace App\Infrastructure\EventBus;

use App\Application\Shared\EventBusInterface;
use Illuminate\Support\Facades\Http;
use DateTimeImmutable;

class HttpEventBus implements EventBusInterface
{
    /**
     * @param string $eventId
     * @param string $name
     * @param array $payload
     * @param DateTimeImmutable $occurredOn
     * @return void
     */
    public function publish(string $eventId, string $name, array $payload, DateTimeImmutable $occurredOn): void
    {
        Http::retry(3, 500, throw: false)->connectTimeout(3)->timeout(env('EVENTBUS_TIMEOUT'))->acceptJson()
            ->asJson()->withHeaders(['X-EventBus-Token' => env('EVENTBUS_SECRET')])
            ->post(
                env("EVENTBUS_ENDPOINT"), 
                [
                    'event' => $name,
                    'occurred_on' => $occurredOn->format(DATE_ATOM),
                    'event_id' => $eventId,
                    'payload' => $payload
                ]
            )->throw();
    }
}