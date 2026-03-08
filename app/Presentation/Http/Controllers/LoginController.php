<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * @class LoginController
 */
class LoginController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $baseUrl = rtrim(config('keycloak.base_url'), '/');
        $realm = config('keycloak.realm');
        $clientId = config('keycloak.client_id');
        $clientSecret = config('keycloak.client_secret');

        $payload = [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'username' => $data['username'],
            'password' => $data['password'],
        ];

        if (! empty($clientSecret)) {
            $payload['client_secret'] = $clientSecret;
        }

        $tokenUrl = $baseUrl . '/realms/' . $realm . '/protocol/openid-connect/token';
        $requireDpop = (bool) config('keycloak.require_dpop', false);
        $request = Http::asForm()
            ->connectTimeout(2)
            ->timeout(5);

        if ($requireDpop) {
            $dpop = $this->buildDpopProof($tokenUrl, 'POST');
            $request = $request->withHeaders(['DPoP' => $dpop]);
        }

        try {
            $response = $request->post($tokenUrl, $payload);
        } catch (Throwable $e) {
            Log::error('Login en Keycloak no disponible', [
                'username' => $data['username'],
                'realm' => $realm,
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'keycloak_unavailable',
                'error_description' => 'Unable to reach identity provider',
            ], 503);
        }

        $body = $response->json();

        if ($response->ok()) {
            Log::info('Login en Keycloak exitoso', [
                'username' => $data['username'],
                'realm' => $realm,
                'client_id' => $clientId,
            ]);
        } else {
            Log::warning('Login en Keycloak fallido', [
                'username' => $data['username'],
                'realm' => $realm,
                'client_id' => $clientId,
                'status' => $response->status(),
                'error' => $body['error'] ?? null,
                'error_description' => $body['error_description'] ?? null,
            ]);
        }

        return response()->json($body, $response->status());
    }

    private function buildDpopProof(string $url, string $method): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $details = openssl_pkey_get_details($key);
        $privateKey = null;
        openssl_pkey_export($key, $privateKey);

        $x = $details['ec']['x'] ?? null;
        $y = $details['ec']['y'] ?? null;

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => $this->base64UrlEncode($x ?: ''),
            'y' => $this->base64UrlEncode($y ?: ''),
        ];

        if (! is_string($privateKey) || $privateKey === '') {
            throw new \RuntimeException('Unable to export DPoP private key');
        }

        $payload = [
            'htu' => $url,
            'htm' => strtoupper($method),
            'iat' => time(),
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, $privateKey, 'ES256', null, [
            'typ' => 'dpop+jwt',
            'jwk' => $jwk,
        ]);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
