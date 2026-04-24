<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

final class Span
{
    /** @var array<string, string|int|float|bool> */
    private array $tags = [];

    private ?float $endMicrotime = null;

    public function __construct(
        public readonly string $name,
        public readonly TraceContext $context,
        public readonly ?string $parentSpanId,
        public readonly float $startMicrotime,
        public readonly string $kind = 'SERVER',
    ) {}

    /** @return array<string, string|int|float|bool> */
    public function tags(): array
    {
        return $this->tags;
    }

    public function setTag(string $key, string|int|float|bool $value): void
    {
        $this->tags[$key] = $value;
    }

    public function end(?float $endMicrotime = null): void
    {
        $this->endMicrotime = $endMicrotime ?? microtime(true);
    }

    public function isEnded(): bool
    {
        return $this->endMicrotime !== null;
    }

    public function durationMicros(): int
    {
        $end = $this->endMicrotime ?? microtime(true);

        return max(0, (int) round(($end - $this->startMicrotime) * 1_000_000));
    }

    public function startTimestampMicros(): int
    {
        return (int) round($this->startMicrotime * 1_000_000);
    }
}
