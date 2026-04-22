<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Infrastructure\Persistence\Model\Outbox;
use App\Infrastructure\Tracing\Tracer;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * @class OutboxStore
 */
class OutboxStore
{
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

        $traceId = null;
        $spanId = null;
        try {
            if (app()->bound(Tracer::class)) {
                $ctx = app(Tracer::class)->currentContext();
                if ($ctx !== null) {
                    $traceId = $ctx->traceId;
                    $spanId = $ctx->spanId;
                }
            }
        } catch (\Throwable $e) {
            $traceId = null;
            $spanId = null;
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
            'trace_id' => $traceId,
            'span_id' => $spanId,
            'payload' => $normalizedPayload,
            'occurred_on' => $occurredOn->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ensures outbound payload carries a UUID in id for downstream consumers.
     */
    private static function normalizePayload(array $payload, string $aggregateId): array
    {
        $id = $payload['id'] ?? null;
        if (! is_string($id) || ! Str::isUuid($id)) {
            $payload['id'] = $aggregateId;
        }

        return $payload;
    }
}
