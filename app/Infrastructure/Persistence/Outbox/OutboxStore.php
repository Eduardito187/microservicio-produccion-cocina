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

    $resolvedAggregateId = is_string($aggregateId) && Str::isUuid($aggregateId)
      ? $aggregateId
      : (string) Str::uuid();
    $normalizedPayload = self::normalizePayload($payload, $resolvedAggregateId);

    Outbox::create([
      'event_id' => (string) Str::uuid(),
      'event_name' => $name,
      'aggregate_id' => $resolvedAggregateId,
      'schema_version' => (int) env('EVENT_SCHEMA_VERSION', 1),
      'correlation_id' => $correlationId ?? (string) Str::uuid(),
      'payload' => $normalizedPayload,
      'occurred_on' => $occurredOn->format('Y-m-d H:i:s'),
    ]);
  }

  /**
   * Ensures outbound payload carries a UUID in id for downstream consumers.
   *
   * @param array $payload
   * @param string $aggregateId
   * @return array
   */
  private static function normalizePayload(array $payload, string $aggregateId): array
  {
    $id = $payload['id'] ?? null;
    if (!is_string($id) || !Str::isUuid($id)) {
      $payload['id'] = $aggregateId;
    }
    return $payload;
  }
}
