<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\ServiceDiscovery;

class ServiceDefinitionFactory
{
    public static function fromConfig(array $config): array
    {
        $serviceName = (string) ($config['service_name'] ?? 'microservicio-produccion-cocina');
        $serviceId = (string) ($config['service_id'] ?? $serviceName);
        $address = (string) ($config['service_address'] ?? 'laravel_app');
        $port = (int) ($config['service_port'] ?? 80);
        $tags = array_values(array_filter((array) ($config['service_tags'] ?? [])));
        $healthPath = (string) ($config['health_path'] ?? '/health');
        $healthInterval = (string) ($config['health_interval'] ?? '15s');
        $healthTimeout = (string) ($config['health_timeout'] ?? '5s');
        $deregisterAfter = (string) ($config['deregister_critical_after'] ?? '1m');

        $healthUrl = sprintf('http://%s:%d%s', $address, $port, $healthPath);

        return [
            'ID' => $serviceId,
            'Name' => $serviceName,
            'Address' => $address,
            'Port' => $port,
            'Tags' => $tags,
            'Check' => [
                'HTTP' => $healthUrl,
                'Method' => 'GET',
                'Interval' => $healthInterval,
                'Timeout' => $healthTimeout,
                'DeregisterCriticalServiceAfter' => $deregisterAfter,
            ],
        ];
    }
}
