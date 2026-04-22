<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

/**
 * Emits spans in Zipkin v2 JSON format.
 * Jaeger's all-in-one image accepts this at :9411/api/v2/spans.
 */
final class ZipkinExporter implements SpanExporterInterface
{
    private ClientInterface $http;
    private string $endpoint;
    private string $serviceName;
    private float $timeout;

    public function __construct(string $endpoint, string $serviceName, float $timeout = 2.0, ?ClientInterface $http = null)
    {
        $this->endpoint = $endpoint;
        $this->serviceName = $serviceName;
        $this->timeout = $timeout;
        $this->http = $http ?? new Client(['timeout' => $timeout]);
    }

    public function export(array $spans): void
    {
        if ($spans === []) {
            return;
        }
        $payload = [];
        foreach ($spans as $span) {
            if (! $span instanceof Span) {
                continue;
            }
            $payload[] = $this->toZipkin($span);
        }
        if ($payload === []) {
            return;
        }

        try {
            $this->http->request('POST', $this->endpoint, [
                RequestOptions::JSON => $payload,
                RequestOptions::HTTP_ERRORS => true,
                RequestOptions::TIMEOUT => $this->timeout,
                RequestOptions::CONNECT_TIMEOUT => $this->timeout,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Zipkin span export failed', [
                'endpoint' => $this->endpoint,
                'spans' => count($payload),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function toZipkin(Span $span): array
    {
        $tags = [];
        foreach ($span->tags() as $key => $value) {
            $tags[$key] = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        }

        $zipkinSpan = [
            'traceId' => $span->context->traceId,
            'id' => $span->context->spanId,
            'name' => $span->name,
            'kind' => $span->kind,
            'timestamp' => $span->startTimestampMicros(),
            'duration' => $span->durationMicros(),
            'localEndpoint' => ['serviceName' => $this->serviceName],
            'tags' => $tags,
        ];
        if ($span->parentSpanId !== null) {
            $zipkinSpan['parentId'] = $span->parentSpanId;
        }

        return $zipkinSpan;
    }
}
