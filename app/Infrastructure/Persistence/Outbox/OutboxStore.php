<?php

namespace App\Infrastructure\Persistence\Outbox;

use App\Infrastructure\Persistence\Model\Outbox;
use Illuminate\Support\Str;
use DateTimeImmutable;

class OutboxStore
{
  /**
   * @param string $name
   * @param string|int|null $aggregateId
   * @param DateTimeImmutable $occurredOn
   * @param array $payload
   * @return void
   */
  public static function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
  {
    Outbox::create([
      'event_name' => $name,
      'aggregate_id' => $aggregateId ?? (string) Str::uuid(),
      'payload' => $payload,
      'occurred_on' => $occurredOn->format('Y-m-d H:i:s'),
    ]);
  }
}