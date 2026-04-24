<?php

/**
 * Microservicio "Produccion y Cocina"
 */

return [
    'driver' => env('SERVICE_DISCOVERY_DRIVER', 'consul'),

    'service_name' => env('SERVICE_NAME', 'microservicio-produccion-cocina'),
    'service_id' => env('SERVICE_ID', null),
    'service_address' => env('SERVICE_ADDRESS', 'laravel_app'),
    'service_port' => (int) env('SERVICE_PORT', 80),
    'service_tags' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SERVICE_TAGS', 'laravel,produccion,cocina'))
    ))),

    'health_path' => env('SERVICE_HEALTH_PATH', '/health'),
    'health_interval' => env('SERVICE_HEALTH_INTERVAL', '15s'),
    'health_timeout' => env('SERVICE_HEALTH_TIMEOUT', '5s'),
    'deregister_critical_after' => env('SERVICE_DEREGISTER_CRITICAL_AFTER', '1m'),

    'consul' => [
        'host' => env('CONSUL_HOST', 'consul'),
        'port' => (int) env('CONSUL_PORT', 8500),
        'scheme' => env('CONSUL_SCHEME', 'http'),
        'timeout' => (float) env('CONSUL_TIMEOUT', 3.0),
        'token' => env('CONSUL_TOKEN'),
    ],
];
