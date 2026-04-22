<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\ServiceDiscovery;

use App\Infrastructure\ServiceDiscovery\ConsulClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

/**
 * @class ConsulClientTest
 */
class ConsulClientTest extends TestCase
{
    public function test_register_puts_json_definition(): void
    {
        $container = [];
        $http = $this->makeClient([new Response(200, [], '')], $container);
        $client = new ConsulClient([], $http);

        $definition = ['ID' => 'svc-1', 'Name' => 'svc'];
        $client->register($definition);

        $this->assertCount(1, $container);
        $sent = $container[0]['request'];
        $this->assertSame('PUT', $sent->getMethod());
        $this->assertStringEndsWith('/v1/agent/service/register', $sent->getUri()->getPath());
        $this->assertSame($definition, json_decode((string) $sent->getBody(), true));
    }

    public function test_deregister_hits_expected_endpoint(): void
    {
        $container = [];
        $http = $this->makeClient([new Response(200, [], '')], $container);
        $client = new ConsulClient([], $http);

        $client->deregister('svc id with spaces');

        $sent = $container[0]['request'];
        $this->assertSame('PUT', $sent->getMethod());
        $this->assertSame('/v1/agent/service/deregister/svc%20id%20with%20spaces', $sent->getUri()->getPath());
    }

    public function test_services_returns_decoded_array(): void
    {
        $http = $this->makeClient([new Response(200, [], json_encode(['a' => ['ID' => 'a']]))]);
        $client = new ConsulClient([], $http);

        $services = $client->services();

        $this->assertSame(['a' => ['ID' => 'a']], $services);
    }

    public function test_services_returns_empty_array_on_non_json(): void
    {
        $http = $this->makeClient([new Response(200, [], 'not-json')]);
        $client = new ConsulClient([], $http);

        $this->assertSame([], $client->services());
    }

    public function test_base_uri_is_built_from_config(): void
    {
        $http = $this->makeClient([]);
        $client = new ConsulClient([
            'host' => 'consul.internal',
            'port' => 18500,
            'scheme' => 'https',
        ], $http);

        $this->assertSame('https://consul.internal:18500', $client->baseUri());
    }

    public function test_token_is_sent_as_header_when_provided(): void
    {
        $container = [];
        $http = $this->makeClient([new Response(200)], $container);
        $client = new ConsulClient(['token' => 'secret-token'], $http);

        $client->register(['Name' => 'svc']);

        $this->assertSame('secret-token', $container[0]['request']->getHeaderLine('X-Consul-Token'));
    }

    private function makeClient(array $responses, array &$container = []): Client
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        return new Client(['handler' => $stack]);
    }
}
