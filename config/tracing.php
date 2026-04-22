<?php

/**
 * Microservicio "Produccion y Cocina"
 */

return [
    'enabled' => filter_var(env('TRACING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'service_name' => env('TRACING_SERVICE_NAME', env('SERVICE_NAME', 'microservicio-produccion-cocina')),
    'sample_rate' => (float) env('TRACING_SAMPLE_RATE', 1.0),

    'exporter' => env('TRACING_EXPORTER', 'zipkin'),

    'zipkin' => [
        'endpoint' => env('ZIPKIN_ENDPOINT', 'http://jaeger:9411/api/v2/spans'),
        'timeout' => (float) env('ZIPKIN_TIMEOUT', 2.0),
    ],
];
