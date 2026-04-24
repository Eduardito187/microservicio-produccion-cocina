<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\ServiceDiscovery;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class ConsulClient
{
    private ClientInterface $http;
    private string $baseUri;
    private float $timeout;
    private ?string $token;

    public function __construct(array $config = [], ?ClientInterface $http = null)
    {
        $host = (string) ($config['host'] ?? 'consul');
        $port = (int) ($config['port'] ?? 8500);
        $scheme = (string) ($config['scheme'] ?? 'http');
        $this->baseUri = sprintf('%s://%s:%d', $scheme, $host, $port);
        $this->timeout = (float) ($config['timeout'] ?? 3.0);
        $this->token = isset($config['token']) && $config['token'] !== '' ? (string) $config['token'] : null;
        $this->http = $http ?? new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
        ]);
    }

    public function register(array $definition): ResponseInterface
    {
        return $this->http->request(
            'PUT',
            '/v1/agent/service/register',
            $this->options([RequestOptions::JSON => $definition])
        );
    }

    public function deregister(string $serviceId): ResponseInterface
    {
        return $this->http->request(
            'PUT',
            '/v1/agent/service/deregister/' . rawurlencode($serviceId),
            $this->options()
        );
    }

    public function services(): array
    {
        $response = $this->http->request('GET', '/v1/agent/services', $this->options());
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function baseUri(): string
    {
        return $this->baseUri;
    }

    private function options(array $extra = []): array
    {
        $opts = array_merge([RequestOptions::HTTP_ERRORS => true], $extra);
        if ($this->token !== null) {
            $opts[RequestOptions::HEADERS] = array_merge(
                $opts[RequestOptions::HEADERS] ?? [],
                ['X-Consul-Token' => $this->token]
            );
        }

        return $opts;
    }
}
