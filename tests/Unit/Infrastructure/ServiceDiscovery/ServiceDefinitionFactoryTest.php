<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\ServiceDiscovery;

use App\Infrastructure\ServiceDiscovery\ServiceDefinitionFactory;
use Tests\TestCase;

/**
 * @class ServiceDefinitionFactoryTest
 */
class ServiceDefinitionFactoryTest extends TestCase
{
    public function test_builds_consul_service_definition_with_defaults(): void
    {
        $definition = ServiceDefinitionFactory::fromConfig([]);

        $this->assertSame('microservicio-produccion-cocina', $definition['Name']);
        $this->assertSame('microservicio-produccion-cocina', $definition['ID']);
        $this->assertSame('laravel_app', $definition['Address']);
        $this->assertSame(80, $definition['Port']);
        $this->assertSame([], $definition['Tags']);
        $this->assertSame('http://laravel_app:80/health', $definition['Check']['HTTP']);
        $this->assertSame('GET', $definition['Check']['Method']);
        $this->assertSame('15s', $definition['Check']['Interval']);
        $this->assertSame('5s', $definition['Check']['Timeout']);
        $this->assertSame('1m', $definition['Check']['DeregisterCriticalServiceAfter']);
    }

    public function test_builds_definition_from_custom_config(): void
    {
        $definition = ServiceDefinitionFactory::fromConfig([
            'service_name' => 'produccion-cocina',
            'service_id' => 'produccion-cocina-1',
            'service_address' => 'app.example',
            'service_port' => 8080,
            'service_tags' => ['laravel', 'produccion'],
            'health_path' => '/api/health',
            'health_interval' => '10s',
            'health_timeout' => '2s',
            'deregister_critical_after' => '5m',
        ]);

        $this->assertSame('produccion-cocina', $definition['Name']);
        $this->assertSame('produccion-cocina-1', $definition['ID']);
        $this->assertSame('app.example', $definition['Address']);
        $this->assertSame(8080, $definition['Port']);
        $this->assertSame(['laravel', 'produccion'], $definition['Tags']);
        $this->assertSame('http://app.example:8080/api/health', $definition['Check']['HTTP']);
        $this->assertSame('10s', $definition['Check']['Interval']);
        $this->assertSame('2s', $definition['Check']['Timeout']);
        $this->assertSame('5m', $definition['Check']['DeregisterCriticalServiceAfter']);
    }

    public function test_filters_empty_tags(): void
    {
        $definition = ServiceDefinitionFactory::fromConfig([
            'service_tags' => ['', 'produccion', null, 'cocina'],
        ]);

        $this->assertSame(['produccion', 'cocina'], $definition['Tags']);
    }
}
