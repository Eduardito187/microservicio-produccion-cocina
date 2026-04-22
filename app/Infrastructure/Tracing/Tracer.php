<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

final class Tracer
{
    private bool $enabled;
    private SpanExporterInterface $exporter;

    /** @var Span[] */
    private array $finishedSpans = [];

    /** @var Span[] */
    private array $stack = [];

    public function __construct(bool $enabled, SpanExporterInterface $exporter)
    {
        $this->enabled = $enabled;
        $this->exporter = $exporter;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function currentContext(): ?TraceContext
    {
        $top = end($this->stack);

        return $top instanceof Span ? $top->context : null;
    }

    public function startSpan(string $name, ?TraceContext $parentContext = null, string $kind = 'SERVER'): Span
    {
        $context = $parentContext !== null
            ? $parentContext->child()
            : TraceContext::generate($this->enabled);

        $parentSpanId = $parentContext?->spanId;

        $span = new Span($name, $context, $parentSpanId, microtime(true), $kind);
        $this->stack[] = $span;

        return $span;
    }

    public function endSpan(Span $span): void
    {
        if (! $span->isEnded()) {
            $span->end();
        }
        foreach ($this->stack as $i => $entry) {
            if ($entry === $span) {
                array_splice($this->stack, $i, 1);
                break;
            }
        }
        if ($this->enabled && $span->context->sampled) {
            $this->finishedSpans[] = $span;
        }
    }

    public function flush(): void
    {
        if (! $this->enabled || $this->finishedSpans === []) {
            $this->finishedSpans = [];

            return;
        }
        $batch = $this->finishedSpans;
        $this->finishedSpans = [];
        $this->exporter->export($batch);
    }

    /** @return Span[] */
    public function pendingSpans(): array
    {
        return $this->finishedSpans;
    }
}
