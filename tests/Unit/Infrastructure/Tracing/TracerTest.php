<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Tracing;

use App\Infrastructure\Tracing\NullExporter;
use App\Infrastructure\Tracing\Span;
use App\Infrastructure\Tracing\SpanExporterInterface;
use App\Infrastructure\Tracing\TraceContext;
use App\Infrastructure\Tracing\Tracer;
use Tests\TestCase;

/**
 * @class TracerTest
 */
class TracerTest extends TestCase
{
    public function test_start_span_without_parent_creates_root_span(): void
    {
        $tracer = new Tracer(true, new NullExporter);

        $span = $tracer->startSpan('root');

        $this->assertNull($span->parentSpanId);
        $this->assertSame('root', $span->name);
        $this->assertSame($span->context, $tracer->currentContext());
    }

    public function test_start_span_with_parent_inherits_trace_id(): void
    {
        $tracer = new Tracer(true, new NullExporter);
        $parent = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), true);

        $span = $tracer->startSpan('child', $parent);

        $this->assertSame($parent->traceId, $span->context->traceId);
        $this->assertSame($parent->spanId, $span->parentSpanId);
        $this->assertNotSame($parent->spanId, $span->context->spanId);
    }

    public function test_end_span_pops_from_stack_and_queues_for_flush(): void
    {
        $tracer = new Tracer(true, new NullExporter);
        $span = $tracer->startSpan('op');

        $tracer->endSpan($span);

        $this->assertNull($tracer->currentContext());
        $this->assertCount(1, $tracer->pendingSpans());
        $this->assertTrue($span->isEnded());
    }

    public function test_flush_exports_finished_spans_and_clears_buffer(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public array $exported = [];

            public function export(array $spans): void
            {
                $this->exported = $spans;
            }
        };
        $tracer = new Tracer(true, $exporter);
        $span = $tracer->startSpan('op');
        $tracer->endSpan($span);

        $tracer->flush();

        $this->assertCount(1, $exporter->exported);
        $this->assertInstanceOf(Span::class, $exporter->exported[0]);
        $this->assertSame([], $tracer->pendingSpans());
    }

    public function test_flush_noop_when_disabled(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public bool $called = false;

            public function export(array $spans): void
            {
                $this->called = true;
            }
        };
        $tracer = new Tracer(false, $exporter);
        $span = $tracer->startSpan('op');
        $tracer->endSpan($span);

        $tracer->flush();

        $this->assertFalse($exporter->called);
    }

    public function test_is_enabled_reflects_constructor(): void
    {
        $this->assertTrue((new Tracer(true, new NullExporter))->isEnabled());
        $this->assertFalse((new Tracer(false, new NullExporter))->isEnabled());
    }

    public function test_nested_spans_maintain_current_context(): void
    {
        $tracer = new Tracer(true, new NullExporter);

        $outer = $tracer->startSpan('outer');
        $inner = $tracer->startSpan('inner', $outer->context);

        $this->assertSame($inner->context, $tracer->currentContext());

        $tracer->endSpan($inner);
        $this->assertSame($outer->context, $tracer->currentContext());

        $tracer->endSpan($outer);
        $this->assertNull($tracer->currentContext());
    }
}
