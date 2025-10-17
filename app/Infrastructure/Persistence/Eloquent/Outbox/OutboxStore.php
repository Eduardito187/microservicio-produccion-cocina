<?php

namespace App\Infrastructure\Persistence\Eloquent\Outbox;

use App\Infrastructure\Persistence\Eloquent\Model\Outbox;
use Illuminate\Support\Str;
use DateTimeImmutable;

class OutboxStore
{
  /**
   * @param string $name
   * @param string $aggregateId
   * @param DateTimeImmutable $occurredOn
   * @param array $payload
   * @return void
   */
  public static function append(string $name, string $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
  {
    Outbox::create([
      'id' => (string) Str::uuid(),
      'event_name' => $name,
      'aggregate_id' => $aggregateId,
      'payload' => $payload,
      'occurred_on' => $occurredOn->format('Y-m-d H:i:s'),
    ]);
  }
}