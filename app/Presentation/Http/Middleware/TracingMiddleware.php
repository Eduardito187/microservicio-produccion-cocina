<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Middleware;

use App\Infrastructure\Tracing\TraceContext;
use App\Infrastructure\Tracing\Tracer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TracingMiddleware
{
    public function __construct(private readonly Tracer $tracer) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tracer->isEnabled()) {
            return $next($request);
        }

        $parent = TraceContext::fromTraceparent($request->headers->get('traceparent'));
        $span = $this->tracer->startSpan(
            sprintf('%s %s', $request->getMethod(), $request->getPathInfo()),
            $parent
        );
        $span->setTag('http.method', $request->getMethod());
        $span->setTag('http.target', $request->getPathInfo());
        $span->setTag('http.scheme', $request->getScheme());
        $host = $request->getHost();
        if ($host !== '') {
            $span->setTag('http.host', $host);
        }

        $traceId = $span->context->traceId;
        $spanId = $span->context->spanId;

        Log::withContext([
            'trace_id' => $traceId,
            'span_id' => $spanId,
        ]);

        try {
            /** @var Response $response */
            $response = $next($request);
            $span->setTag('http.status_code', $response->getStatusCode());
            if ($response->getStatusCode() >= 500) {
                $span->setTag('error', true);
            }
            $response->headers->set('traceparent', $span->context->toTraceparent());
            $response->headers->set('X-Trace-Id', $traceId);

            return $response;
        } catch (\Throwable $e) {
            $span->setTag('error', true);
            $span->setTag('error.message', $e->getMessage());
            throw $e;
        } finally {
            $this->tracer->endSpan($span);
            $this->tracer->flush();
        }
    }
}
