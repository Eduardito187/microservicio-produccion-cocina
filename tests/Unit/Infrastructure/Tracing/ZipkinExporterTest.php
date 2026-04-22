<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Tracing;

use App\Infrastructure\Tracing\Span;
use App\Infrastructure\Tracing\TraceContext;
use App\Infrastructure\Tracing\ZipkinExporter;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

/**
 * @class ZipkinExporterTest
 */
class ZipkinExporterTest extends TestCase
{
    public function test_export_sends_zipkin_v2_payload(): void
    {
        $container = [];
        $mock = new MockHandler([new Response(202)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));
        $http = new Client(['handler' => $stack]);

        $exporter = new ZipkinExporter('http://jaeger:9411/api/v2/spans', 'test-service', 2.0, $http);

        $ctx = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), true);
        $span = new Span('GET /hello', $ctx, null, microtime(true) - 0.001, 'SERVER');
        $span->setTag('http.status_code', 200);
        $span->setTag('error', false);
        $span->end();

        $exporter->export([$span]);

        $this->assertCount(1, $container);
        /** @var \Psr\Http\Message\RequestInterface $sent */
        $sent = $container[0]['request'];
        $this->assertSame('POST', $sent->getMethod());
        $this->assertSame('http://jaeger:9411/api/v2/spans', (string) $sent->getUri());
        $body = json_decode((string) $sent->getBody(), true);
        $this->assertIsArray($body);
        $this->assertCount(1, $body);
        $this->assertSame(str_repeat('a', 32), $body[0]['traceId']);
        $this->assertSame(str_repeat('b', 16), $body[0]['id']);
        $this->assertSame('GET /hello', $body[0]['name']);
        $this->assertSame('SERVER', $body[0]['kind']);
        $this->assertSame(['serviceName' => 'test-service'], $body[0]['localEndpoint']);
        $this->assertSame('200', $body[0]['tags']['http.status_code']);
        $this->assertSame('false', $body[0]['tags']['error']);
        $this->assertArrayNotHasKey('parentId', $body[0]);
    }

    public function test_export_includes_parent_id_for_child_spans(): void
    {
        $mock = new MockHandler([new Response(202)]);
        $stack = HandlerStack::create($mock);
        $container = [];
        $stack->push(Middleware::history($container));
        $http = new Client(['handler' => $stack]);
        $exporter = new ZipkinExporter('http://jaeger:9411/api/v2/spans', 'svc', 2.0, $http);

        $ctx = new TraceContext(str_repeat('1', 32), str_repeat('2', 16), true);
        $span = new Span('child', $ctx, str_repeat('9', 16), microtime(true), 'CONSUMER');
        $span->end();

        $exporter->export([$span]);

        $body = json_decode((string) $container[0]['request']->getBody(), true);
        $this->assertSame(str_repeat('9', 16), $body[0]['parentId']);
        $this->assertSame('CONSUMER', $body[0]['kind']);
    }

    public function test_export_does_nothing_for_empty_batch(): void
    {
        $mock = new MockHandler([]);
        $stack = HandlerStack::create($mock);
        $http = new Client(['handler' => $stack]);
        $exporter = new ZipkinExporter('http://jaeger:9411/api/v2/spans', 'svc', 2.0, $http);

        $exporter->export([]);

        $this->assertSame(0, $mock->count());
    }

    public function test_export_swallows_http_errors(): void
    {
        $mock = new MockHandler([new Response(503)]);
        $stack = HandlerStack::create($mock);
        $http = new Client(['handler' => $stack]);
        $exporter = new ZipkinExporter('http://jaeger:9411/api/v2/spans', 'svc', 2.0, $http);

        $ctx = new TraceContext(str_repeat('a', 32), str_repeat('b', 16), true);
        $span = new Span('op', $ctx, null, microtime(true), 'SERVER');
        $span->end();

        $exporter->export([$span]);

        $this->assertTrue(true);
    }
}
