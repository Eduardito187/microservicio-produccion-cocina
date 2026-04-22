<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Http\Middleware;

use App\Infrastructure\Tracing\NullExporter;
use App\Infrastructure\Tracing\SpanExporterInterface;
use App\Infrastructure\Tracing\TraceContext;
use App\Infrastructure\Tracing\Tracer;
use App\Presentation\Http\Middleware\TracingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Tests\TestCase;

/**
 * @class TracingMiddlewareTest
 */
class TracingMiddlewareTest extends TestCase
{
    public function test_passes_through_when_tracing_disabled(): void
    {
        $tracer = new Tracer(false, new NullExporter);
        $middleware = new TracingMiddleware($tracer);
        $request = Request::create('/api/health', 'GET');

        $response = $middleware->handle($request, fn () => new IlluminateResponse('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('traceparent'));
        $this->assertFalse($response->headers->has('X-Trace-Id'));
    }

    public function test_starts_span_and_adds_trace_headers_to_response(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public array $exported = [];

            public function export(array $spans): void
            {
                $this->exported = array_merge($this->exported, $spans);
            }
        };
        $tracer = new Tracer(true, $exporter);
        $middleware = new TracingMiddleware($tracer);
        $request = Request::create('/api/orders', 'POST');

        $response = $middleware->handle($request, fn () => new IlluminateResponse('created', 201));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($response->headers->has('traceparent'));
        $this->assertTrue($response->headers->has('X-Trace-Id'));
        $this->assertCount(1, $exporter->exported);
        $tags = $exporter->exported[0]->tags();
        $this->assertSame('POST', $tags['http.method']);
        $this->assertSame('/api/orders', $tags['http.target']);
        $this->assertSame(201, $tags['http.status_code']);
    }

    public function test_continues_parent_trace_from_incoming_header(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public array $exported = [];

            public function export(array $spans): void
            {
                $this->exported = array_merge($this->exported, $spans);
            }
        };
        $parent = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), true);
        $tracer = new Tracer(true, $exporter);
        $middleware = new TracingMiddleware($tracer);
        $request = Request::create('/api/health', 'GET');
        $request->headers->set('traceparent', $parent->toTraceparent());

        $response = $middleware->handle($request, fn () => new IlluminateResponse('ok', 200));

        $this->assertCount(1, $exporter->exported);
        $span = $exporter->exported[0];
        $this->assertSame($parent->traceId, $span->context->traceId);
        $this->assertSame($parent->spanId, $span->parentSpanId);
        $this->assertStringContainsString($parent->traceId, $response->headers->get('traceparent'));
        $this->assertSame($parent->traceId, $response->headers->get('X-Trace-Id'));
    }

    public function test_tags_5xx_as_error(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public array $exported = [];

            public function export(array $spans): void
            {
                $this->exported = array_merge($this->exported, $spans);
            }
        };
        $tracer = new Tracer(true, $exporter);
        $middleware = new TracingMiddleware($tracer);
        $request = Request::create('/api/broken', 'GET');

        $middleware->handle($request, fn () => new IlluminateResponse('boom', 503));

        $tags = $exporter->exported[0]->tags();
        $this->assertSame(503, $tags['http.status_code']);
        $this->assertTrue($tags['error']);
    }

    public function test_exception_tags_error_and_is_rethrown(): void
    {
        $exporter = new class implements SpanExporterInterface
        {
            public array $exported = [];

            public function export(array $spans): void
            {
                $this->exported = array_merge($this->exported, $spans);
            }
        };
        $tracer = new Tracer(true, $exporter);
        $middleware = new TracingMiddleware($tracer);
        $request = Request::create('/api/fail', 'GET');

        try {
            $middleware->handle($request, function (): void {
                throw new \RuntimeException('boom');
            });
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertCount(1, $exporter->exported);
        $tags = $exporter->exported[0]->tags();
        $this->assertTrue($tags['error']);
        $this->assertSame('boom', $tags['error.message']);
    }
}
