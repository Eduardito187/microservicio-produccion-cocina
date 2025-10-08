<?php

namespace App\Infrastructure\EventBus;

use App\Application\Shared\EventBus;
use Illuminate\Support\Facades\Http;
use DateTimeImmutable;

class HttpEventBus implements EventBus
{
    /**
     * @var string
     */
    private readonly string $endpoint;

    /**
     * Constructor
     * @param string $endpoint
     */
    public function __construct(string $endpoint) {
        $this->endpoint = $endpoint;
    }

    /**
     * @param string $name
     * @param array $payload
     * @param DateTimeImmutable $occurredOn
     * @return void
     */
    public function publish(string $name, array $payload, DateTimeImmutable $occurredOn): void
    {
        Http::timeout(3)->post($this->endpoint, [
            'event' => $name,
            'occurred_on' => $occurredOn->format(DATE_ATOM),
            'payload' => $payload,
        ])->throw();
    }
}