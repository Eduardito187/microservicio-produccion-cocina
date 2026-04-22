<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Tracing;

use App\Infrastructure\Tracing\TraceContext;
use Tests\TestCase;

/**
 * @class TraceContextTest
 */
class TraceContextTest extends TestCase
{
    public function test_generate_produces_valid_ids_and_sampled_flag(): void
    {
        $ctx = TraceContext::generate(true);

        $this->assertSame(32, strlen($ctx->traceId));
        $this->assertSame(16, strlen($ctx->spanId));
        $this->assertTrue(ctype_xdigit($ctx->traceId));
        $this->assertTrue(ctype_xdigit($ctx->spanId));
        $this->assertTrue($ctx->sampled);
    }

    public function test_to_traceparent_formats_w3c_header(): void
    {
        $ctx = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), true);

        $this->assertSame('00-' . str_repeat('a', 32) . '-' . str_repeat('b', 16) . '-01', $ctx->toTraceparent());
    }

    public function test_to_traceparent_emits_flag_zero_when_not_sampled(): void
    {
        $ctx = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), false);

        $this->assertStringEndsWith('-00', $ctx->toTraceparent());
    }

    public function test_from_traceparent_parses_valid_header(): void
    {
        $header = '00-' . str_repeat('1', 32) . '-' . str_repeat('2', 16) . '-01';

        $ctx = TraceContext::fromTraceparent($header);

        $this->assertInstanceOf(TraceContext::class, $ctx);
        $this->assertSame(str_repeat('1', 32), $ctx->traceId);
        $this->assertSame(str_repeat('2', 16), $ctx->spanId);
        $this->assertTrue($ctx->sampled);
    }

    public function test_from_traceparent_returns_null_on_invalid_input(): void
    {
        $this->assertNull(TraceContext::fromTraceparent(null));
        $this->assertNull(TraceContext::fromTraceparent(''));
        $this->assertNull(TraceContext::fromTraceparent('not-a-header'));
        $this->assertNull(TraceContext::fromTraceparent('01-' . str_repeat('a', 32) . '-' . str_repeat('b', 16) . '-01'));
        $this->assertNull(TraceContext::fromTraceparent('00-xyz-' . str_repeat('b', 16) . '-01'));
        $this->assertNull(TraceContext::fromTraceparent('00-' . str_repeat('0', 32) . '-' . str_repeat('b', 16) . '-01'));
        $this->assertNull(TraceContext::fromTraceparent('00-' . str_repeat('a', 32) . '-' . str_repeat('0', 16) . '-01'));
    }

    public function test_child_keeps_trace_id_and_generates_new_span_id(): void
    {
        $parent = TraceContext::generate();
        $child = $parent->child();

        $this->assertSame($parent->traceId, $child->traceId);
        $this->assertNotSame($parent->spanId, $child->spanId);
        $this->assertSame($parent->sampled, $child->sampled);
    }

    public function test_round_trip_to_and_from_traceparent(): void
    {
        $original = TraceContext::generate(true);
        $recovered = TraceContext::fromTraceparent($original->toTraceparent());

        $this->assertInstanceOf(TraceContext::class, $recovered);
        $this->assertSame($original->traceId, $recovered->traceId);
        $this->assertSame($original->spanId, $recovered->spanId);
        $this->assertSame($original->sampled, $recovered->sampled);
    }
}
