<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Infrastructure\Persistence\Model\Outbox;
use Illuminate\Support\Str;
use DateTimeImmutable;

/**
 * @class OutboxStore
 * @package App\Infrastructure\Persistence\Outbox
 */
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
    $correlationId = null;
    try {
      $header = request()->header('X-Correlation-Id');
      if (is_string($header) && $header !== '') {
        $correlationId = $header;
      }
    } catch (\Throwable $e) {
      $correlationId = null;
    }

    Outbox::create([
      'event_id' => (string) Str::uuid(),
      'event_name' => $name,
      'aggregate_id' => $aggregateId ?? (string) Str::uuid(),
      'schema_version' => (int) env('EVENT_SCHEMA_VERSION', 1),
      'correlation_id' => $correlationId ?? (string) Str::uuid(),
      'payload' => $payload,
      'occurred_on' => $occurredOn->format('Y-m-d H:i:s'),
    ]);
  }
}
