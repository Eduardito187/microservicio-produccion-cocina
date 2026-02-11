<?php
/**
 * Microservicio "Produccion y Cocina"
 */

return [
    'base_url' => env('KEYCLOAK_BASE_URL', 'http://keycloak:8080'),
    'realm' => env('KEYCLOAK_REALM', 'classroom'),
    'client_id' => env('KEYCLOAK_CLIENT_ID', 'api-gateway'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', null),
    'issuer' => env('KEYCLOAK_ISSUER', 'http://keycloak:8080/realms/classroom'),
    'jwks_ttl' => (int) env('KEYCLOAK_JWKS_TTL', 600),
    'require_dpop' => (bool) env('KEYCLOAK_REQUIRE_DPOP', false),
];
